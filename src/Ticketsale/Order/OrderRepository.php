<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Ticketsale\Concert\Concert;

/**
 * @implements RepositoryInterface<Order>
 */
final class OrderRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Order::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @param Concert $concert
     * @return Order[]
     */
    public function fetchByConcert(Concert $concert): array
    {
        return $this->fetchAll(['concertId = ?'], [$concert->id]);
    }
}
