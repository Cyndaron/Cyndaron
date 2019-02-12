<?php
declare(strict_types = 1);

namespace Cyndaron;

use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class Controller
{
    protected $module = null;
    protected $action = null;

    protected $minLevelGet = UserLevel::ANONYMOUS;
    protected $minLevelPost = UserLevel::ADMIN;

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
                $this->checkUserLevelOrDie($this->minLevelGet);
                $this->routeGet();
                break;
            case 'POST':
                $this->checkUserLevelOrDie($this->minLevelPost);
                $this->routePost();
                break;
        }
    }

    public function routeGet() {}

    public function routePost() {}

    public function sendErrorMessage(string $message): void
    {
        echo json_encode([
            'error' => $message
        ]);
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

    public function send500(string $message = 'Internal server error'): void
    {
        header('HTTP/1.1 500 Internal Server Error');
        $this->sendErrorMessage($message);
    }

    public function checkUserLevelOrDie(int $requiredLevel): void
    {
        if (!User::isLoggedIn())
        {
            session_destroy();
            session_start();
            User::addNotification('U moet inloggen om deze pagina te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            header('Location: /login');
            die();
        }
        else if (User::getLevel() < $requiredLevel)
        {
            $this->send403('Insufficient user rights!');
            die();
        }
    }
}
