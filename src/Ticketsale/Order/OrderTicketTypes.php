<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\TicketType;
use function assert;

final class OrderTicketTypes extends Model
{
    public const TABLE = 'ticketsale_orders_tickettypes';
    public const TABLE_FIELDS = ['orderId', 'tickettypeId','amount', 'secretCode', 'hasBeenScanned'];

    public int $orderId;
    public int $tickettypeId;
    /** @deprecated  */
    public int $amount = 1;
    public ?string $secretCode;
    public bool $hasBeenScanned = false;

    private ?Order $order = null;
    private ?TicketType $ticketType = null;

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
            $this->order = Order::loadFromDatabase($this->orderId);
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
            $this->ticketType = TicketType::loadFromDatabase($this->tickettypeId);
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
