<?php
namespace Cyndaron\Kaartverkoop;

class Util extends \Cyndaron\Util
{
    const SEATS_PER_ROW = 330;

    public static function postcodeIsWithinWalcheren(int $postcode)
    {
        if ($postcode >= 4330 && $postcode <= 4399)
            return true;
        else
            return false;
    }
}