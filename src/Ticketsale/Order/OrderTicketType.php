<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Ticketsale\TicketType;

final class OrderTicketType
{
    public Order $order;
    public TicketType $ticketType;
    public int $amount;
}
