<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\Concert\ConcertRepository;
use Cyndaron\Ticketsale\TicketType\TicketTypeRepository;
use Cyndaron\Url\UrlService;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use function random_int;

final class Util
{
    public const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode): bool
    {
        return ($postcode >= 4330 && $postcode <= 4399);
    }

    public static function drawPageManagerTab(
        TemplateRenderer $templateRenderer,
        UrlService $urlService,
        Request $request,
        CSRFTokenHandler $tokenHandler,
        ConcertRepository $concertRepository,
        TicketTypeRepository $ticketTypeRepository,
    ): string {
        $templateVars = [
            'concerts' => $concertRepository->fetchAll(afterWhere: 'ORDER BY date DESC'),
            'urlService' => $urlService,
            'baseUrl' => $request->getSchemeAndHttpHost(),
            'tokenDelete' => $tokenHandler->get('concert', 'delete'),
            'ticketTypeRepository' => $ticketTypeRepository,
        ];
        return $templateRenderer->render('Ticketsale/PageManagerTab', $templateVars);
    }

    public static function generateSecretCode(): string
    {
        return (string)random_int(1_000_000_000, 9_999_999_999);
    }
}
