<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;
use Cyndaron\Template\Template;

class Util extends \Cyndaron\Util
{
    public const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode): bool
    {
        if ($postcode >= 4330 && $postcode <= 4399)
            return true;
        else
            return false;
    }

    public static function getLatestConcertId(): ?int
    {
        return DBConnection::doQueryAndFetchOne('SELECT MAX(id) FROM ticketsale_concerts');
    }

    public static function drawPageManagerTab(): string
    {
        $templateVars = ['concerts' => Concert::fetchAll()];
        return (new Template())->render('Ticketsale/PageManagerTab', $templateVars);

    }
}