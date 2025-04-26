<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;

final class OrderHelper
{
    public function __construct(private readonly OrderTicketTypesRepository $orderTicketTypesRepository)
    {
    }

    public function calculateOrderTotal(Order $order): float
    {
        $orderTicketTypes = $this->orderTicketTypesRepository->fetchAllByOrder($order);
        $totalCost = $this->getDeliveryCost($order)->getCost();
        $reservedSeatCharge = $order->hasReservedSeats ? $order->concert->reservedSeatCharge : 0.00;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $ticketType = $orderTicketType->ticketType;
            $totalCost += $orderTicketType->amount * $ticketType->price;
            $totalCost += $orderTicketType->amount * $reservedSeatCharge;
        }

        return $totalCost;
    }

    public function getDeliveryCost(Order $order): DeliveryCostInterface
    {
        $orderTicketTypes = $this->orderTicketTypesRepository->fetchAllByOrder($order);
        $interfaceName = $order->concert->getDeliveryCostInterface();
        /** @var DeliveryCostInterface $object */
        $object = new $interfaceName($order->concert, $order, $orderTicketTypes);
        return $object;
    }
}
