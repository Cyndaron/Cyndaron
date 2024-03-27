<?php
declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\Page\SimplePage;
use Cyndaron\Routing\Controller;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\DependencyInjectionContainer;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;

final class ErrorController extends Controller
{
    public array $getRoutes = [
        '' => ['function' => 'show', 'level' => UserLevel::ANONYMOUS],
    ];
    public array $postRoutes = [
        '' => ['function' => 'show', 'level' => UserLevel::ANONYMOUS, 'skipCSRFCheck' => true],
    ];

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

    public function show(): Response
    {
        $code = (int)$this->action;

        if (!array_key_exists($code, self::KNOWN_ERRORS))
        {
            $page = new SimplePage('Onbekende fout', 'Er is een onbekende fout opgetreden. Code: ' . $code);
            return new Response($page->render());
        }

        $error = self::KNOWN_ERRORS[$code];
        $page = new SimplePage($error['pageTitle'], $error['notification']);
        return new Response($page->render(), $code);
    }
}
