<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\View\Template\Template;
use function random_int;

final class Util extends \Cyndaron\Util\Util
{
    public const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode): bool
    {
        return ($postcode >= 4330 && $postcode <= 4399);
    }

    public static function drawPageManagerTab(): string
    {
        $templateVars = ['concerts' => Concert::fetchAll()];
        return (new Template())->render('Ticketsale/PageManagerTab', $templateVars);
    }

    public static function generateSecretCode(): string
    {
        return (string)random_int(1_000_000_000, 9_999_999_999);
    }
}
