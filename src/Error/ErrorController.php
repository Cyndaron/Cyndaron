<?php
declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\UserLevel;
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

    #[RouteAttribute('', RequestMethod::GET, UserLevel::ANONYMOUS)]
    #[RouteAttribute('', RequestMethod::POST, UserLevel::ANONYMOUS, skipCSRFCheck: true)]
    public function show(QueryBits $queryBits): Response
    {
        $code = $queryBits->getInt(1);

        if (!array_key_exists($code, self::KNOWN_ERRORS))
        {
            $page = new SimplePage('Onbekende fout', 'Er is een onbekende fout opgetreden. Code: ' . $code);
            return $this->pageRenderer->renderResponse($page);
        }

        $error = self::KNOWN_ERRORS[$code];
        $page = new SimplePage($error['pageTitle'], $error['notification']);
        return $this->pageRenderer->renderResponse($page, status: $code);
    }
}
