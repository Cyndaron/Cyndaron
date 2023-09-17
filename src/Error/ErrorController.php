<?php
declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\Page\SimplePage;
use Cyndaron\Routing\Controller;
use Cyndaron\Util\DependencyInjectionContainer;
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

        if (!array_key_exists($this->action, self::KNOWN_ERRORS))
        {
            $page = new SimplePage('Onbekende fout', 'Er is een onbekende fout opgetreden. Code: ' . $this->action);
            return new Response($page->render());
        }

        $error = self::KNOWN_ERRORS[$this->action];
        $statusCode = (int)$this->action;
        $page = new SimplePage($error['pageTitle'], $error['notification']);
        return new Response($page->render(), $statusCode);
    }
}
