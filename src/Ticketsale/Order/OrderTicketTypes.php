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

    #[DatabaseField]
    public int $orderId;
    #[DatabaseField]
    public int $tickettypeId;
    /** @deprecated  */
    #[DatabaseField]
    public int $amount = 1;
    #[DatabaseField]
    public string|null $secretCode;
    #[DatabaseField]
    public bool $hasBeenScanned = false;

    private Order|null $order = null;
    private TicketType|null $ticketType = null;

    public function setOrder(Order $order): void
    {
        assert($order->id !== null);
        $this->order = $order;
        $this->orderId = $order->id;
    }

    public function getOrder(): Order
    {
        if ($this->order === null)
        {
            $this->order = Order::fetchById($this->orderId);
        }

        assert($this->order !== null);
        return $this->order;
    }

    public function setTicketType(TicketType $ticketType): void
    {
        assert($ticketType->id !== null);
        $this->ticketType = $ticketType;
        $this->tickettypeId = $ticketType->id;
    }

    public function getTicketType(): TicketType
    {
        if ($this->ticketType === null)
        {
            $this->ticketType = TicketType::fetchById($this->tickettypeId);
        }

        assert($this->ticketType !== null);
        return $this->ticketType;
    }

    public function getPrice(): float
    {
        $price = $this->getTicketType()->price;
        $order = $this->getOrder();
        if ($order->hasReservedSeats)
        {
            $price += $order->getConcert()->reservedSeatCharge;
        }

        return $price;
    }
}
