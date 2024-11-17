<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

enum Currency : string
{
    case EURO = 'EUR';
    case LOTTERY_TICKET = 'LOT';
}
