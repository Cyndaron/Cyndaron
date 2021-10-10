<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\DeliveryCost;

use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\Order\Order;

final class FlatFee implements DeliveryCostInterface
{
    private Concert $concert;
    private Order $order;
    private array $ticketTypes;

    public function __construct(Concert $concert, Order $order, array $ticketTypes)
    {
        $this->concert = $concert;
        $this->order = $order;
        $this->ticketTypes = $ticketTypes;
    }

    public function getCost(): float
    {
        if (!$this->order->delivery)
        {
            return 0.0;
        }

        $deliveryCost = $this->concert->deliveryCost;
        $totalCost = 0.0;
        foreach ($this->ticketTypes as $ticketType)
        {
            $totalCost += ($ticketType->amount * $deliveryCost);
        }

        return $totalCost;
    }
}
