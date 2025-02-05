<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

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
        return $this->fetchAll(['orderId = ?'], [$order->id]);
    }
}
