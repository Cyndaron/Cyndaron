<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Url\UrlService;
use Cyndaron\View\Template\TemplateRenderer;
use function random_int;

final class Util
{
    public const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode): bool
    {
        return ($postcode >= 4330 && $postcode <= 4399);
    }

    public static function drawPageManagerTab(TemplateRenderer $templateRenderer, UrlService $urlService): string
    {
        $templateVars = ['concerts' => Concert::fetchAll(), 'urlService' => $urlService];
        return $templateRenderer->render('Ticketsale/PageManagerTab', $templateVars);
    }

    public static function generateSecretCode(): string
    {
        return (string)random_int(1_000_000_000, 9_999_999_999);
    }
}
