<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

final class OrderTotal
{
    public float $amount = 0.00;
    public bool $payForDelivery = false;
    public int $numTickets = 0;
    public array $ticketTypes = [];
}
