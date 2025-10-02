<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function array_filter;

/**
 * @implements RepositoryInterface<OrderTicketType>
 */
final class OrderTicketTypeRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = OrderTicketType::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @param Order $order
     * @return OrderTicketType[]
     */
    public function fetchAllByOrder(Order $order): array
    {
        /** @var OrderTicketType[] $items */
        $items = array_filter(
            $this->fetchAll(),
            function(OrderTicketType $candidate) use ($order)
            {
                return $candidate->order->id === $order->id;
            },
        );
        return $items;
    }
}
