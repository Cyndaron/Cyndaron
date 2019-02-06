<?php
declare(strict_types = 1);

namespace Cyndaron;

use Cyndaron\User\User;

abstract class Controller
{
    protected $module = null;
    protected $action = null;

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

    public abstract function route();

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
}
