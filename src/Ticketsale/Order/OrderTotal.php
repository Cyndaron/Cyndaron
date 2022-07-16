<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

final class OrderTotal
{
    public float $amount = 0.00;
    public bool $payForDelivery = false;
    public int $numTickets = 0;
    /** @var OrderTicketTypes[] */
    public array $ticketTypes = [];
}
