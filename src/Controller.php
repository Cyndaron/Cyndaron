<?php
declare (strict_types = 1);

namespace Cyndaron;

use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Controller
{
    protected ?string $module = null;
    protected ?string $action = null;
    protected bool $isApiCall = false;

    protected int $minLevelGet = UserLevel::ANONYMOUS;
    protected int $minLevelPost = UserLevel::ADMIN;

    protected array $getRoutes = [];
    protected array $postRoutes = [];
    protected array $apiGetRoutes = [];
    protected array $apiPostRoutes = [];

    protected QueryBits $queryBits;

    public function __construct(string $module, string $action, bool $isApiCall = false)
    {
        $this->module = $module;
        $this->action = $action;
        $this->isApiCall = $isApiCall;
    }

    public function checkCSRFToken(string $token): bool
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !User::checkToken($this->module, $this->action, $token))
        {
            return false;
        }

        return true;
    }

    public function route(RequestParameters $post)
    {
        $getRoutes = ($this->isApiCall && !empty($this->apiGetRoutes)) ? $this->apiGetRoutes : $this->getRoutes;
        $postRoutes = ($this->isApiCall && !empty($this->apiPostRoutes)) ? $this->apiPostRoutes : $this->postRoutes;

        switch($_SERVER['REQUEST_METHOD'])
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
                    return new JsonResponse(['error' => 'Unacceptable request method!'], Response::HTTP_METHOD_NOT_ALLOWED, ['allow' => 'GET, POST']);

                $page = new Page('Verkeerde aanvraag', 'U kunt geen aanvraag doen met deze methode.');
                return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }

        if (array_key_exists($this->action, $routesTable))
        {
            $route = $routesTable[$this->action];
            $right = $route['right'] ?? '';
            $hasRight = $right !== '' && !empty($_SESSION['profile']) && $_SESSION['profile']->hasRight($right);
            if (!$hasRight)
            {
                $level = $route['level'] ?? UserLevel::ADMIN;
                $this->checkUserLevelOrDie($level);
            }

            $function = $route['function'];
            return $this->$function($post);
        }

        // Do not fall back to old functions for API calls.
        if ($this->isApiCall)
        {
            return new JsonResponse(['error' => 'Route not found!'], Response::HTTP_NOT_FOUND);
        }

        $this->checkUserLevelOrDie($oldMinLevel);
        return $this->$oldRouteFunction($post);
    }

    protected function routeGet(): Response
    {
        return new Response('Route niet gevonden!', Response::HTTP_NOT_FOUND);
    }

    /** @noinspection PhpUnusedParameterInspection */
    protected function routePost(RequestParameters $post): Response
    {
        return new Response('Route niet gevonden!', Response::HTTP_NOT_FOUND);
    }

    public function checkUserLevelOrDie(int $requiredLevel): void
    {
        if ($requiredLevel > UserLevel::ANONYMOUS && !User::isLoggedIn())
        {
            session_destroy();
            session_start();
            User::addNotification('U moet inloggen om deze pagina te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];

            $response = new RedirectResponse('/user/login', );
            $response->send();
            die();
        }
        if (User::getLevel() < $requiredLevel)
        {
            $response = new Response('Insufficient user rights!', Response::HTTP_FORBIDDEN);
            $response->send();
            die();
        }
    }

    public function setQueryBits(QueryBits $queryBits): void
    {
        $this->queryBits = $queryBits;
    }
}
