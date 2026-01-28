<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use function assert;
use function is_int;
use function array_key_exists;
use function floor;

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
        $ticketTotal = $this->calculateOrderTicketTotal($orderTicketTypes, $reservedSeatCharge);
        $totalCost += $ticketTotal['totalPrice'];

        return $totalCost;
    }

    /**
     * @param OrderTicketTypes[] $orderTicketTypes
     * @param float $reservedSeatCharge
     * @return array{totalPrice: float, totalNumTickets: int}
     */
    public function calculateOrderTicketTotal(array $orderTicketTypes, float $reservedSeatCharge): array
    {
        $totalPrice = 0.0;
        $totalNumTickets = 0;

        /** @var array<int, array{ price: float, amount: int, discountPer5: bool }> $amountsPerType */
        $amountsPerType = [];

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $ticketType = $orderTicketType->ticketType;
            assert(is_int($ticketType->id));
            if (!array_key_exists($ticketType->id, $amountsPerType))
            {
                $amountsPerType[$ticketType->id] = [
                    'price' => $ticketType->price,
                    'amount' => 0,
                    'discountPer5' => $ticketType->discountPer5,
                ];
            }

            $amountsPerType[$ticketType->id]['amount'] += $orderTicketType->amount;
        }

        foreach ($amountsPerType as $info)
        {
            $billedAmount = $info['amount'];
            if ($info['discountPer5'])
            {
                $numFree = (int)floor($info['amount'] / 5);
                $billedAmount -= $numFree;
            }
            $ticketPrice = $info['price'] + $reservedSeatCharge;
            $totalPrice += $billedAmount * $ticketPrice;
            $totalNumTickets += $info['amount'];
        }

        return ['totalPrice' => $totalPrice, 'totalNumTickets' => $totalNumTickets];
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
