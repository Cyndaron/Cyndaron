<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\View\Page;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\View\SimplePage;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function method_exists;
use function Safe\session_destroy;
use function array_key_exists;
use function is_array;
use function session_start;

abstract class Controller
{
    protected ?string $module = null;
    protected ?string $action = null;
    protected bool $isApiCall = false;

    protected int $minLevelGet = UserLevel::ANONYMOUS;
    protected int $minLevelPost = UserLevel::ADMIN;

    /** @var array[] $getRoutes */
    protected array $getRoutes = [];
    /** @var array[] $postRoutes */
    protected array $postRoutes = [];
    /** @var array[] $apiGetRoutes */
    protected array $apiGetRoutes = [];
    /** @var array[] $apiPostRoutes */
    protected array $apiPostRoutes = [];

    /** @deprecated  */
    protected QueryBits $queryBits;

    public function __construct(string $module, string $action, bool $isApiCall = false)
    {
        $this->module = $module;
        $this->action = $action;
        $this->isApiCall = $isApiCall;
    }

    public function checkCSRFToken(string $token): bool
    {
        if ($this->module === null || $this->action === null)
        {
            return false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !User::checkToken($this->module, $this->action, $token))
        {
            return false;
        }

        return true;
    }

    public function route(DependencyInjectionContainer $dic): Response
    {
        $getRoutes = ($this->isApiCall && !empty($this->apiGetRoutes)) ? $this->apiGetRoutes : $this->getRoutes;
        $postRoutes = ($this->isApiCall && !empty($this->apiPostRoutes)) ? $this->apiPostRoutes : $this->postRoutes;

        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $routesTable = $getRoutes;
                $oldRouteFunction = 'routeGet';
                $oldMinLevel = $this->minLevelGet;
                break;
            case 'POST':
                $routesTable = $postRoutes;
                $oldRouteFunction = 'routePost';
                $oldMinLevel = $this->minLevelPost;
                break;
            default:
                if ($this->isApiCall)
                {
                    return new JsonResponse(['error' => 'Unacceptable request method!'], Response::HTTP_METHOD_NOT_ALLOWED, ['allow' => 'GET, POST']);
                }

                $page = new SimplePage('Verkeerde aanvraag', 'U kunt geen aanvraag doen met deze methode.');
                return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        if ($this->action !== null && array_key_exists($this->action, $routesTable))
        {
            $route = $routesTable[$this->action];
            if (is_array($route))
            {
                $route = new Route($route['function'], $route['level']?? UserLevel::ADMIN, $route['right'] ?? null);
            }
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

            return $this->callMethodWithDependencyInjection($route->method, $dic);
        }

        // Do not fall back to old functions for API calls.
        if ($this->isApiCall)
        {
            return new JsonResponse(['error' => 'Route not found!'], Response::HTTP_NOT_FOUND);
        }

        // Fall back to old functions
        if (!method_exists($this, $oldRouteFunction))
        {
            return new Response('Route niet gevonden!', Response::HTTP_NOT_FOUND);
        }

        $response = $this->checkUserLevel($oldMinLevel);
        if ($response !== null)
        {
            return $response;
        }

        return $this->callMethodWithDependencyInjection($oldRouteFunction, $dic);
    }

    protected function callMethodWithDependencyInjection(string $method, DependencyInjectionContainer $dic): Response
    {
        $reflectionMethod = new \ReflectionMethod($this, $method);
        $reflectionMethod->setAccessible(true);

        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            $className = ($type instanceof ReflectionNamedType) ? $type->getName() : '';

            $params[] = $dic->get($className);
        }
        return $reflectionMethod->invokeArgs($this, $params);
    }

    /**
     * @param int $requiredLevel
     * @throws \Safe\Exceptions\SessionException
     * @throws \Safe\Exceptions\SessionException
     * @return Response|null A Response if the user level is insufficient, null otherwise.
     */
    public function checkUserLevel(int $requiredLevel): ?Response
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

    public function setQueryBits(QueryBits $queryBits): void
    {
        $this->queryBits = $queryBits;
    }
}
