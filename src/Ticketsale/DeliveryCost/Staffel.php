<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\DeliveryCost;

use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\Order\Order;

final class Staffel implements DeliveryCostInterface
{
    private Order $order;
    private array $ticketTypes;

    /** @phpstan-ignore-next-line */
    public function __construct(Concert $concert, Order $order, array $ticketTypes)
    {
        $this->order = $order;
        $this->ticketTypes = $ticketTypes;
    }

    private function getCostByAmount(int $totalNumTickets): float
    {
        if ($totalNumTickets === 0)
        {
            return 0.00;
        }
        if ($totalNumTickets === 1)
        {
            return 2.00;
        }
        if ($totalNumTickets === 2)
        {
            return 3.00;
        }
        if ($totalNumTickets >= 3 && $totalNumTickets <= 7)
        {
            return 4.00;
        }

        return 5.00;
    }

    public function getCost(): float
    {
        if (!$this->order->delivery)
        {
            return 0.0;
        }

        $totalNumTickets = 0;
        foreach ($this->ticketTypes as $ticketType)
        {
            $totalNumTickets += $ticketType->amount;
        }

        return $this->getCostByAmount($totalNumTickets);
    }
}
