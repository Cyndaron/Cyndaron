<?php
declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\Routing\Controller;
use Cyndaron\View\Page;
use Cyndaron\Routing\DependencyInjectionContainer;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;

final class ErrorController extends Controller
{
    public const KNOWN_ERRORS = [
        '403' => [
            'pageTitle' => '403: Forbidden',
            'notification' => 'U heeft geprobeerd een pagina op te vragen die niet mag worden opgevraagd.',
        ],
        '404' => [
            'pageTitle' => '404: Not Found',
            'notification' => 'U heeft geprobeerd een pagina op te vragen die niet kon worden gevonden.',
        ],
    ];

    // Overridden, since both GET and POST requests may end up here, and checking user rights is not necessary.
    public function route(DependencyInjectionContainer $dic): Response
    {
        if ($this->action === null)
        {
            $this->action = 'null';
        }

        if (!array_key_exists($this->action, static::KNOWN_ERRORS))
        {
            $page = new Page('Onbekende fout', 'Er is een onbekende fout opgetreden. Code: ' . $this->action);
            return new Response($page->render());
        }

        $error = static::KNOWN_ERRORS[$this->action];
        $statusCode = (int)$this->action;
        $page = new Page($error['pageTitle'], $error['notification']);
        return new Response($page->render(), $statusCode);
    }
}
