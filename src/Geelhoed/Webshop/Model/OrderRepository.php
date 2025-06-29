<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Geelhoed\Clubactie\Subscriber;

/**
 * @implements RepositoryInterface<Order>
 */
final class OrderRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Order::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly OrderItemRepository $orderItemRepository
    ) {
    }

    public function fetchBySubscriber(Subscriber $subscriber): Order|null
    {
        return $this->fetch(['subscriberId = ?'], [$subscriber->id]);
    }

    public function confirmByUser(Order $order): OrderStatus
    {
        if ($order->status !== OrderStatus::QUOTE)
        {
            throw new \Exception('Order kan niet nogmaals bevestigd worden!');
        }

        $needsTicketCheck = ($this->getTicketTotal($order) > 0) && (!$order->subscriber->soldTicketsAreVerified);
        if ($needsTicketCheck)
        {
            $order->status = OrderStatus::PENDING_TICKET_CHECK;
        }
        else
        {
            if ($this->getEuroSubtotal($order) === 0.00)
            {
                $order->status = OrderStatus::IN_PROGRESS;
            }
            else
            {
                $order->status = OrderStatus::PENDING_PAYMENT;
            }
        }

        return $order->status;
    }

    public function getEuroSubtotal(Order $order): float
    {
        $subtotal = 0.00;
        $items = $this->orderItemRepository->fetchAllByOrder($order);
        foreach ($items as $item)
        {
            if ($item->currency === Currency::EURO)
            {
                $subtotal += $item->getLineAmount();
            }
        }

        return $subtotal;
    }

    public function getTicketTotal(Order $order): int
    {
        $subtotal = 0;
        $items = $this->orderItemRepository->fetchAllByOrder($order);
        foreach ($items as $item)
        {
            if ($item->currency === Currency::LOTTERY_TICKET)
            {
                $subtotal += (int)$item->getLineAmount();
            }
        }

        return $subtotal;
    }
}
