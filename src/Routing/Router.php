<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\Connection;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Translation\Translator;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserRepository;
use Cyndaron\User\UserSession;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Setting;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function ltrim;
use function parse_url;
use function str_starts_with;
use function strpos;
use function substr;
use const PHP_URL_PATH;

final class Router
{
    private Connection $connection;
    private ModuleRegistry $moduleRegistry;
    private UrlService $urlService;

    public function __construct(
        private readonly DependencyInjectionContainer $dic,
        private readonly PageRenderer $pageRenderer,
    ) {
        $this->connection = $dic->get(Connection::class);
        $this->moduleRegistry = $dic->get(ModuleRegistry::class);
        $this->urlService = $dic->get(UrlService::class);
    }

    private function sendMethodNotAllowed(bool $isApiCall): Response
    {
        if ($isApiCall)
        {
            return new JsonResponse(['error' => 'Unacceptable request method!'], Response::HTTP_METHOD_NOT_ALLOWED, ['allow' => 'GET, POST']);
        }

        $page = new SimplePage('Verkeerde aanvraag', 'U kunt geen aanvraag doen met deze methode.');
        return $this->pageRenderer->renderResponse($page, status: Response::HTTP_METHOD_NOT_ALLOWED, headers: ['allow' => 'GET, POST']);
    }

    private function sendNotFound(bool $isApiCall): Response
    {
        if ($isApiCall)
        {
            return new JsonResponse(['error' => 'Route not found!'], Response::HTTP_NOT_FOUND);
        }

        $page = new SimplePage('Fout', 'Deze route is niet bekend.');
        return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
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
        if ($this->urlService->equals($frontPage, new Url($request)))
        {
            return new RedirectResponse('/', Response::HTTP_MOVED_PERMANENTLY);
        }
        // Redirect if a friendly url exists for the requested unfriendly url
        if ($request !== '/' && $url = $this->connection->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target = ?', [$request]))
        {
            return new RedirectResponse("/$url", Response::HTTP_MOVED_PERMANENTLY);
        }

        return null;
    }

    private function containsPathTraversal(string $request): bool
    {
        return ($request !== '/' && (substr($request, 0, 1) === '.' || substr($request, 0, 1) === '/'));
    }

    private function getLoginStatus(QueryBits $queryBits, UserSession $userSession): LoginStatus
    {
        $isLoggingIn = $queryBits->getString(0) === 'user' && $queryBits->getString(1) === 'login';
        if ($isLoggingIn)
        {
            return LoginStatus::OK;
        }

        if ($userSession->hasSufficientReadLevel())
        {
            return LoginStatus::OK;
        }

        $userLevel = $userSession->getLevel();
        if ($userLevel > UserLevel::ANONYMOUS)
        {
            return LoginStatus::INSUFFICIENT_RIGHTS;
        }

        return LoginStatus::NEEDS_LOGIN;
    }

    public function route(Request $request): Response
    {
        $requestUri = $request->getRequestUri();
        $requestStr = parse_url($requestUri, PHP_URL_PATH) ?: '';
        $requestStr = ltrim($requestStr, '/') ?: '/';

        if ($this->containsPathTraversal($requestStr))
        {
            return new RedirectResponse('/error/403');
        }

        $redirect = $this->getRedirect($request->getRequestUri());
        if ($redirect)
        {
            return $redirect;
        }

        $isApiCall = str_starts_with($requestStr, 'api/');
        $queryBits = $this->rewriteFriendlyUrls($requestStr);
        $module = $queryBits->getString(0);
        $action = $queryBits->getString(1);

        $controllers = $this->moduleRegistry->controllers;

        if (!array_key_exists($module, $controllers))
        {
            return $this->sendNotFound($isApiCall);
        }

        $userSession = $this->dic->get(UserSession::class);
        $redirect = $this->getLoginRedirect($queryBits, $userSession, $requestUri);
        if ($redirect !== null)
        {
            return $redirect;
        }

        $post = new RequestParameters($request->request->all());
        $urlInfo = UrlInfo::fromRequest($request);

        $this->dic->add($request);
        $this->dic->add($post);
        $this->dic->add($queryBits);
        $this->dic->add($urlInfo);
        $tokenHandler = $this->dic->get(CSRFTokenHandler::class);
        $profile = $userSession->getProfile();
        if ($profile !== null)
        {
            $this->dic->add($profile);
        }

        $classname = $controllers[$module];
        $controller = $this->dic->createClassWithDependencyInjection($classname);

        $requestMethod = RequestMethod::tryFrom($request->getRealMethod());
        if ($requestMethod === null)
        {
            return $this->sendMethodNotAllowed($isApiCall);
        }

        // Try exact match first, if none is found, try a catch-all (if it exists).
        $route = $this->moduleRegistry->getRoute($module, $action, $requestMethod, $isApiCall);
        if ($route === null)
        {
            $route = $this->moduleRegistry->getRoute($module, '', $requestMethod, $isApiCall);
        }
        if ($route === null)
        {
            return $this->sendNotFound($isApiCall);
        }

        if (!$route->skipCSRFCheck)
        {
            $post = $this->dic->tryGet(RequestParameters::class);
            $token = $post !== null ? $post->getAlphaNum('csrfToken') : '';
            $tokenCorrect = $this->checkCSRFToken($requestMethod, $tokenHandler, $module, $action, $token);
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

        $userRepository = $this->dic->get(UserRepository::class);
        return $this->callRoute($controller, $route, $userSession, $userRepository, $requestUri);
    }

    public function getLoginRedirect(QueryBits $queryBits, UserSession $userSession, string $requestUri): RedirectResponse|null
    {
        $loginStatus = $this->getLoginStatus($queryBits, $userSession);
        switch ($loginStatus)
        {
            case LoginStatus::OK:
                return null;
            case LoginStatus::INSUFFICIENT_RIGHTS:
                return new RedirectResponse('/error/403', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
            case LoginStatus::NEEDS_LOGIN:
            default:
                $userSession->addNotification('U moet inloggen om deze site te bekijken');
                $userSession->setRedirect($requestUri);
                return new RedirectResponse('/user/login', Response::HTTP_FOUND, Kernel::HEADERS_DO_NOT_CACHE);
        }
    }

    public function checkCSRFToken(RequestMethod $requestMethod, CSRFTokenHandler $tokenHandler, string $module, string $action, string $token): bool
    {
        if ($requestMethod === RequestMethod::POST && !$tokenHandler->check($module, $action, $token))
        {
            return false;
        }

        return true;
    }

    private function callRoute(object $controller, Route $route, UserSession $userSession, UserRepository $userRepository, string $requestUri): Response
    {
        $right = $route->right;
        $profile = $userSession->getProfile();
        $hasRight = !empty($right) && $profile !== null && $userRepository->userHasRight($profile, $right);
        if (!$hasRight)
        {
            $response = $this->checkUserLevel($userSession, $route->level, $requestUri);
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
        if ($url = $this->connection->doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [$request]))
        {
            return QueryBits::fromString($this->rewriteFriendlyUrl(new Url($url)));
        }

        return $queryBits;
    }

    /**
     * @param Url $url
     * @return string
     */
    private function rewriteFriendlyUrl(Url $url): string
    {
        $ufUrl = (string)$this->urlService->toUnfriendly($url);
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

    private function callMethodWithDependencyInjection(object $controller, string $method): Response
    {
        /** @var Response $response */
        $response = $this->dic->callMethodWithDependencyInjection($controller, $method);
        return $response;
    }

    /**
     * @param UserSession $userSession
     * @param int $requiredLevel
     * @param string $requestUri
     * @return Response|null A Response if the user level is insufficient, null otherwise.
     */
    public function checkUserLevel(UserSession $userSession, int $requiredLevel, string $requestUri): Response|null
    {
        if ($requiredLevel > UserLevel::ANONYMOUS && !$userSession->isLoggedIn())
        {
            $t = $this->dic->get(Translator::class);
            $userSession->invalidate();
            $userSession->addNotification($t->get('U moet inloggen om deze pagina te bekijken.'));
            $userSession->setRedirect($requestUri);

            return new RedirectResponse('/user/login', );
        }
        if ($userSession->getLevel() < $requiredLevel)
        {
            return new Response('Insufficient user rights!', Response::HTTP_FORBIDDEN);
        }

        return null;
    }
}
