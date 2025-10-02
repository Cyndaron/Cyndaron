<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Geelhoed\Tryout\Tryout;

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
     * @param Tryout $event
     * @return Order[]
     */
    public function fetchByEvent(Tryout $event): array
    {
        return $this->fetchAll(['tryoutId = ?'], [$event->id]);
    }
}
