<?php
declare (strict_types = 1);

namespace Cyndaron\Error;

use Cyndaron\Controller;
use Cyndaron\Page;
use Cyndaron\Request;

class ErrorController extends Controller
{
    const KNOWN_ERRORS = [
        '403' => [
            'httpStatus' => 'HTTP/1.0 403 Forbidden',
            'pageTitle' => '403: Forbidden',
            'notification' => 'U heeft geprobeerd een pagina op te vragen die niet mag worden opgevraagd.',
        ],
        '404' => [
            'httpStatus' => 'HTTP/1.0 404 Not Found',
            'pageTitle' => '404: Not Found',
            'notification' => 'U heeft geprobeerd een pagina op te vragen die niet kon worden gevonden.',
        ],
    ];

    // Overridden, since both GET and POST requests may end up here, and checking user rights is not necessary.
    public function route()
    {
        $errorCode = Request::getVar(1);
        if (array_key_exists($this->action, static::KNOWN_ERRORS))
        {
            $error = static::KNOWN_ERRORS[$this->action];
            header($error['httpStatus']);
            $page = new Page($error['pageTitle'], $error['notification']);
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
        else
        {
            $page = new Page('Onbekende fout', 'Er is een onbekende foutopgetreden. Code: ' . $this->action);
            $page->showPrePage();
            $page->showBody();
            $page->showPostPage();
        }
    }
}