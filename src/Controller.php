<?php
declare (strict_types = 1);

namespace Cyndaron;

use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class Controller
{
    protected $module = null;
    protected $action = null;

    protected $minLevelGet = UserLevel::ANONYMOUS;
    protected $minLevelPost = UserLevel::ADMIN;

    protected $getRoutes = [];
    protected $postRoutes = [];

    public function __construct(string $module, string $action)
    {
        $this->module = $module;
        $this->action = $action;
    }

    public function checkCSRFToken(string $token): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            if (!User::checkToken($this->module, $this->action, $token))
            {
                $this->send403('Controle CSRF-token gefaald!');
                die();
            }
        }
    }

    public function route()
    {
        switch($_SERVER['REQUEST_METHOD'])
        {
            case 'GET':
                $routesTable = $this->getRoutes;
                $oldRouteFunction = 'routeGet';
                $oldMinLevel = $this->minLevelGet;
                break;
            case 'POST':
                $routesTable = $this->postRoutes;
                $oldRouteFunction = 'routePost';
                $oldMinLevel = $this->minLevelPost;
                break;
            default:
                $this->send400();
                die();
        }

        if (array_key_exists($this->action, $routesTable))
        {
            $route = $routesTable[$this->action];
            $level = $route['minLevel'] ?? UserLevel::ADMIN;
            $this->checkUserLevelOrDie($level);
            $function = $route['function'];
            $this->$function();
        }
        else
        {
            $this->checkUserLevelOrDie($oldMinLevel);
            $this->$oldRouteFunction();
        }
    }

    protected function routeGet()
    {
        $this->send404('Route niet gevonden!');
    }

    protected function routePost()
    {
        $this->send404('Route niet gevonden!');
    }

    public function sendErrorMessage(string $message): void
    {
        echo json_encode([
            'error' => $message
        ]);
    }

    public function send400(string $message = 'Bad request'): void
    {
        header('HTTP/1.1 400 Bad Request');
        $this->sendErrorMessage($message);
    }

    public function send401(string $message = 'Not authorised'): void
    {
        header('HTTP/1.1 401 Unauthorized');
        $this->sendErrorMessage($message);
    }

    public function send403(string $message = 'Forbidden'): void
    {
        header('HTTP/1.1 403 Forbidden');
        $this->sendErrorMessage($message);
    }

    public function send404(string $message = 'Not found'): void
    {
        header('HTTP/1.1 404 Not Found');
        $this->sendErrorMessage($message);
    }

    public function send500(string $message = 'Internal server error'): void
    {
        header('HTTP/1.1 500 Internal Server Error');
        $this->sendErrorMessage($message);
    }

    public function checkUserLevelOrDie(int $requiredLevel): void
    {
        if ($requiredLevel > 0 && !User::isLoggedIn())
        {
            session_destroy();
            session_start();
            User::addNotification('U moet inloggen om deze pagina te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            header('Location: /user/login');
            die();
        }
        else if (User::getLevel() < $requiredLevel)
        {
            $this->send403('Insufficient user rights!');
            die();
        }
    }
}
