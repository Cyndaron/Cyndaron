<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\DeliveryCost;

use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\Order\Order;

interface DeliveryCostInterface
{
    public function __construct(Concert $concert, Order $order, array $ticketTypes);

    public function getCost(): float;
}
