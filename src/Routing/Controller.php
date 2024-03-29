<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Page\SimplePage;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use ReflectionNamedType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function is_array;
use function method_exists;
use function Safe\session_destroy;
use function session_start;

abstract class Controller
{
    protected string|null $module = null;
    protected string|null $action = null;
    protected bool $isApiCall = false;

    /** @var array<string, array{function: string, level?: int, right?: string }> */
    protected array $getRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string }> */
    protected array $postRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string }> */
    protected array $apiGetRoutes = [];
    /** @var array<string, array{function: string, level?: int, right?: string }> */
    protected array $apiPostRoutes = [];

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

    /**
     * @param array{function: string, level?: int, right?: string }|Route $route
     * @return Response
     */
    private function callRoute(array|Route $route, DependencyInjectionContainer $dic): Response
    {
        if (is_array($route))
        {
            $route = new Route($route['function'], $route['level'] ?? UserLevel::ADMIN, $route['right'] ?? null);
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

        return $this->callMethodWithDependencyInjection($route->function, $dic);
    }

    public function route(DependencyInjectionContainer $dic): Response
    {
        $getRoutes = ($this->isApiCall && !empty($this->apiGetRoutes)) ? $this->apiGetRoutes : $this->getRoutes;
        $postRoutes = ($this->isApiCall && !empty($this->apiPostRoutes)) ? $this->apiPostRoutes : $this->postRoutes;

        switch ($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $routesTable = $getRoutes;
                break;
            case 'POST':
                $routesTable = $postRoutes;
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
            return $this->callRoute($routesTable[$this->action], $dic);
        }
        if (array_key_exists('', $routesTable))
        {
            return $this->callRoute($routesTable[''], $dic);
        }

        return new JsonResponse(['error' => 'Route not found!'], Response::HTTP_NOT_FOUND);
    }

    protected function callMethodWithDependencyInjection(string $method, DependencyInjectionContainer $dic): Response
    {
        $reflectionMethod = new \ReflectionMethod($this, $method);

        $params = [];
        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $type = $parameter->getType();
            $className = ($type instanceof ReflectionNamedType) ? $type->getName() : '';

            $params[] = $dic->get($className);
        }

        /** @var Response $ret */
        $ret = $reflectionMethod->invokeArgs($this, $params);
        return $ret;
    }

    /**
     * @param int $requiredLevel
     * @throws \Safe\Exceptions\SessionException
     * @throws \Safe\Exceptions\SessionException
     * @return Response|null A Response if the user level is insufficient, null otherwise.
     */
    public function checkUserLevel(int $requiredLevel):Response|null
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
