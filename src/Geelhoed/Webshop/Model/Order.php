<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Hour\Hour;
use function assert;

final class Order extends Model
{
    public const TABLE = 'geelhoed_webshop_order';

    #[DatabaseField]
    public int $subscriberId;
    #[DatabaseField]
    public int $hourId = 1;
    #[DatabaseField]
    public OrderStatus $status = OrderStatus::QUOTE;
    #[DatabaseField]
    public string $paymentId = '';

    public static function fetchBySubscriber(Subscriber $subscriber): self|null
    {
        return self::fetch(['subscriberId = ?'], [$subscriber->id]);
    }

    public function getSubscriber(): Subscriber
    {
        $subscriber = Subscriber::fetchById($this->subscriberId);
        assert($subscriber !== null);
        return $subscriber;
    }

    public function getEuroSubtotal(): float
    {
        $subtotal = 0.00;
        $items = OrderItem::fetchAllByOrder($this);
        foreach ($items as $item)
        {
            if ($item->currency === Currency::EURO)
            {
                $subtotal += $item->getLineAmount();
            }
        }

        return $subtotal;
    }

    public function getTicketTotal(): int
    {
        $subtotal = 0;
        $items = OrderItem::fetchAllByOrder($this);
        foreach ($items as $item)
        {
            if ($item->currency === Currency::LOTTERY_TICKET)
            {
                $subtotal += (int)$item->getLineAmount();
            }
        }

        return $subtotal;
    }

    public function confirmByUser(): OrderStatus
    {
        if ($this->status !== OrderStatus::QUOTE)
        {
            throw new \Exception('Order kan niet nogmaals bevestigd worden!');
        }

        $subscriber = $this->getSubscriber();
        $needsTicketCheck = ($this->getTicketTotal() > 0) && (!$subscriber->soldTicketsAreVerified);
        if ($needsTicketCheck)
        {
            $this->status = OrderStatus::PENDING_TICKET_CHECK;
        }
        else
        {
            if ($this->getEuroSubtotal() === 0.00)
            {
                $this->status = OrderStatus::IN_PROGRESS;
            }
            else
            {
                $this->status = OrderStatus::PENDING_PAYMENT;
            }
        }

        return $this->status;
    }

    public function getHour(): Hour
    {
        $hour = Hour::fetchById($this->hourId);
        assert($hour !== null);
        return $hour;
    }
}
