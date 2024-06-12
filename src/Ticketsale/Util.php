<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\Ticketsale\Concert\Concert;
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

    public static function drawPageManagerTab(TemplateRenderer $templateRenderer, UrlService $urlService, Request $request, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [
            'concerts' => Concert::fetchAll(),
            'urlService' => $urlService,
            'baseUrl' => $request->getSchemeAndHttpHost(),
            'tokenDelete' => $tokenHandler->get('concert', 'delete'),
        ];
        return $templateRenderer->render('Ticketsale/PageManagerTab', $templateVars);
    }

    public static function generateSecretCode(): string
    {
        return (string)random_int(1_000_000_000, 9_999_999_999);
    }
}
