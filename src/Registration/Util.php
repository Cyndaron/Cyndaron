<?php
namespace Cyndaron\Registration;

use Cyndaron\DBConnection;

class Util extends \Cyndaron\Util
{
    const MAX_RESERVED_SEATS = 330;

    public static function postcodeQualifiesForFreeDelivery(int $postcode)
    {
        if ($postcode >= 4330 && $postcode <= 4399)
            return true;
        else
            return false;
    }

    public static function getLatestEventId(): ?int
    {
        return DBConnection::doQueryAndFetchOne('SELECT MAX(id) FROM registration_events');
    }
}