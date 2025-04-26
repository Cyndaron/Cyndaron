<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<OrderTicketTypes>
 */
final class OrderTicketTypesRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = OrderTicketTypes::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    /**
     * @param Order $order
     * @return OrderTicketTypes[]
     */
    public function fetchAllByOrder(Order $order): array
    {
        return $this->fetchAll(['orderId = ?'], [$order->id]);
    }
}
