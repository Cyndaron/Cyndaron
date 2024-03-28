<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Setting;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function in_array;
use function ltrim;
use function parse_url;
use function Safe\session_destroy;
use function session_start;
use function str_starts_with;
use function strpos;
use function substr;
use const PHP_URL_PATH;

final class Router
{
    public function __construct(
        private readonly DependencyInjectionContainer $dic,
        private readonly ModuleRegistry $moduleRegistry,
        private readonly PageRenderer $pageRenderer,
    ) {
    }

    public function findRoute(string|null $action, Controller $controller, bool $isApiCall): Route|null
    {
        $getRoutes = ($isApiCall && !empty($controller->apiGetRoutes)) ? $controller->apiGetRoutes : $controller->getRoutes;
        $postRoutes = ($isApiCall && !empty($controller->apiPostRoutes)) ? $controller->apiPostRoutes : $controller->postRoutes;

        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $routesTable = $getRoutes;
                break;
            case 'POST':
                $routesTable = $postRoutes;
                break;
            default:
                return null;
        }

        /** @var array{function: string, level?: int, right?: string, skipCSRFCheck?: bool }|null $route */
        $route = null;
        if ($action !== null && array_key_exists($action, $routesTable))
        {
            $route = $routesTable[$action];
        }
        if (array_key_exists('', $routesTable))
        {
            $route = $routesTable[''];
        }

        if ($route === null)
        {
            return null;
        }

        return new Route(
            $route['function'],
            $route['level'] ?? UserLevel::ADMIN,
            $route['right'] ?? null,
            $route['skipCSRFCheck'] ?? false,
        );
    }

    private function sendNotFound(bool $isApiCall): Response
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['GET', 'POST'], true))
        {
            if ($isApiCall)
            {
                return new JsonResponse(['error' => 'Unacceptable request method!'], Response::HTTP_METHOD_NOT_ALLOWED, ['allow' => 'GET, POST']);
            }

            $page = new SimplePage('Verkeerde aanvraag', 'U kunt geen aanvraag doen met deze methode.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['error' => 'Route not found!'], Response::HTTP_NOT_FOUND);
    }

    private function getFrontpageUrl(): Url
    {
        return new Url(Setting::get('frontPage') ?: '');
    }

    /**
     * @param string $request
     * @return RedirectResponse|null
     */
    private function getRedirect(string $request): RedirectResponse|null
    {
        $frontPage = $this->getFrontpageUrl();
        if ($frontPage->equals(new Url($request)))
        {
            return new RedirectResponse('/', Response::HTTP_MOVED_PERMANENTLY);
        }
        // Redirect if a friendly url exists for the requested unfriendly url
        if ($_SERVER['REQUEST_URI'] !== '/' && $url = DBConnection::getPDO()->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target = ?', [$request]))
        {
            return new RedirectResponse("/$url", Response::HTTP_MOVED_PERMANENTLY);
        }

        return null;
    }

    private function containsPathTraversal(string $request): bool
    {
        return ($request !== '/' && (substr($request, 0, 1) === '.' || substr($request, 0, 1) === '/'));
    }

    private function getLoginStatus(QueryBits $queryBits): LoginStatus
    {
        $isLoggingIn = $queryBits->getString(0) === 'user' && $queryBits->getString(1) === 'login';
        if ($isLoggingIn)
        {
            return LoginStatus::OK;
        }

        if (User::hasSufficientReadLevel())
        {
            return LoginStatus::OK;
        }

        $userLevel = User::getLevel();
        if ($userLevel > UserLevel::ANONYMOUS)
        {
            return LoginStatus::INSUFFICIENT_RIGHTS;
        }

        return LoginStatus::NEEDS_LOGIN;
    }

    public function route(Request $request): Response
    {
        $requestStr = parse_url($request->getRequestUri(), PHP_URL_PATH) ?: '';
        $requestStr = ltrim($requestStr, '/') ?: '/';

        if ($this->containsPathTraversal($requestStr))
        {
            return new RedirectResponse('/error/403');
        }

        $redirect = $this->getRedirect($_SERVER['REQUEST_URI']);
        if ($redirect)
        {
            return $redirect;
        }

        $isApiCall = str_starts_with($requestStr, '/api');
        $queryBits = $this->rewriteFriendlyUrls($requestStr);
        $module = $queryBits->getString(0);
        $action = $queryBits->getString(1);

        $controllers = $this->moduleRegistry->controllers;

        if (!array_key_exists($module, $controllers))
        {
            return $this->sendNotFound($isApiCall);
        }

        $redirect = $this->getLoginRedirect($queryBits);
        if ($redirect !== null)
        {
            return $redirect;
        }

        $post = new RequestParameters($request->request->all());

        $this->dic->add($request);
        $this->dic->add($post);
        $this->dic->add($queryBits);
        if (isset($_SESSION['profile']))
        {
            $this->dic->add($_SESSION['profile']);
        }

        $classname = $controllers[$module];
        /** @var Controller $controller */
        $controller = new $classname($module, $action, $isApiCall, $this->pageRenderer);

        $route = $this->findRoute($action, $controller, $isApiCall);
        if ($route === null)
        {
            return $this->sendNotFound($isApiCall);
        }

        if (!$route->skipCSRFCheck)
        {
            $post = $this->dic->tryGet(RequestParameters::class);
            $token = $post !== null ? $post->getAlphaNum('csrfToken') : '';
            $tokenCorrect = $this->checkCSRFToken($_SERVER['REQUEST_METHOD'], $module, $action, $token);
            if (!$tokenCorrect)
            {
                if ($isApiCall)
                {
                    return new JsonResponse(['error' => 'CSRF token incorrect!'], Response::HTTP_FORBIDDEN);
                }

                $page = new SimplePage('Controle CSRF-token gefaald!', 'Uw CSRF-token is niet correct.');
                return $this->pageRenderer->renderResponse($page, status: Response::HTTP_FORBIDDEN);
            }
        }

        return $this->callRoute($controller, $route);
    }

    public function getLoginRedirect(QueryBits $queryBits): RedirectResponse|null
    {
        $loginStatus = $this->getLoginStatus($queryBits);
        switch ($loginStatus)
        {
            case LoginStatus::OK:
                return null;
            case LoginStatus::INSUFFICIENT_RIGHTS:
                return new RedirectResponse('/error/403', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
            case LoginStatus::NEEDS_LOGIN:
            default:
                User::addNotification('U moet inloggen om deze site te bekijken');
                $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                return new RedirectResponse('/user/login', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
        }
    }

    public function checkCSRFToken(string $requestMethod, string $module, string $action, string $token): bool
    {
        if ($requestMethod === 'POST' && !User::checkToken($module, $action, $token))
        {
            return false;
        }

        return true;
    }

    private function callRoute(Controller $controller, Route $route): Response
    {
        $right = $route->right;
        $hasRight = !empty($right) && !empty($_SESSION['profile']) && $_SESSION['profile']->hasRight($right);
        if (!$hasRight)
        {
            $response = $this->checkUserLevel($route->level);
            if ($response !== null)
            {
                return $response;
            }
        }

        return $this->callMethodWithDependencyInjection($controller, $route->function);
    }

    private function rewriteFriendlyUrls(string $request): QueryBits
    {
        $queryBits = QueryBits::fromString($request);
        // Frontpage
        if ($queryBits->getString(0) === '')
        {
            $frontpage = $this->getFrontpageUrl();
            return QueryBits::fromString((string)$frontpage);
        }
        // Known friendly URL
        if ($url = DBConnection::getPDO()->doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [$request]))
        {
            return QueryBits::fromString($this->rewriteFriendlyUrl(new Url($url)));
        }

        return $queryBits;
    }

    /**
     * @param Url $url
     * @throws \Safe\Exceptions\StringsException
     * @return string
     */
    private function rewriteFriendlyUrl(Url $url): string
    {
        $ufUrl = $url->getUnfriendly();
        $qmPos = strpos($ufUrl, '?');
        if ($qmPos !== false)
        {
            $file = substr($ufUrl, 0, $qmPos);
        }
        else
        {
            $file = $ufUrl;
        }
        return $file;
    }

    private function callMethodWithDependencyInjection(Controller $controller, string $method): Response
    {
        $reflectionMethod = new \ReflectionMethod($controller, $method);

        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            /** @var class-string $className */
            $className = ($type instanceof ReflectionNamedType) ? $type->getName() : '';

            $params[] = $this->dic->tryGet($className);
        }

        /** @var Response $ret */
        $ret = $reflectionMethod->invokeArgs($controller, $params);
        return $ret;
    }

    /**
     * @param int $requiredLevel
     * @throws \Safe\Exceptions\SessionException
     * @throws \Safe\Exceptions\SessionException
     * @return Response|null A Response if the user level is insufficient, null otherwise.
     */
    public function checkUserLevel(int $requiredLevel): Response|null
    {
        if ($requiredLevel > UserLevel::ANONYMOUS && !User::isLoggedIn())
        {
            session_destroy();
            session_start();
            User::addNotification('U moet inloggen om deze pagina te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];

            return new RedirectResponse('/user/login', );
        }
        if (User::getLevel() < $requiredLevel)
        {
            return new Response('Insufficient user rights!', Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
