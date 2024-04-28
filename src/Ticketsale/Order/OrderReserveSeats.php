<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

enum OrderReserveSeats : int
{
    case RESERVE = 1;
    case NOT_RESERVED = 0;
    case FAILED_RESERVE = -1;
}
