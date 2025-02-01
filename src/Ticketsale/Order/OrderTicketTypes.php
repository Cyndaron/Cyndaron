<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\TicketType\TicketType;
use function assert;

final class OrderTicketTypes extends Model
{
    public const TABLE = 'ticketsale_orders_tickettypes';

    #[DatabaseField('orderId')]
    public Order $order;
    #[DatabaseField('tickettypeId')]
    public TicketType $ticketType;
    /** @deprecated  */
    #[DatabaseField]
    public int $amount = 1;
    #[DatabaseField]
    public string|null $secretCode;
    #[DatabaseField]
    public bool $hasBeenScanned = false;

    public function getPrice(): float
    {
        $price = $this->ticketType->price;
        if ($this->order->hasReservedSeats)
        {
            $price += $this->order->concert->reservedSeatCharge;
        }

        return $price;
    }
}
