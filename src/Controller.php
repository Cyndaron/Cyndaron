<?php
declare(strict_types = 1);

namespace Cyndaron;

class Controller
{
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

    public function send500(string $message = 'Internal server error'): void
    {
        header('HTTP/1.1 500 Internal Server Error');
        $this->sendErrorMessage($message);
    }
}
