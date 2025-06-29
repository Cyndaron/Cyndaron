<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function array_filter;

/**
 * @implements RepositoryInterface<OrderItem>
 */
final class OrderItemRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = OrderItem::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @return OrderItem[]
     */
    public function fetchAllByOrder(Order $order): array
    {
        return array_filter($this->fetchAll(), static function(OrderItem $orderItem) use ($order)
        {
            return $orderItem->order->id = $order->id;
        });
    }
}
