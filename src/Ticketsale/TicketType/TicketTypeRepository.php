<?php

declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Ticketsale\Concert\Concert;
use function usort;

/**
 * @implements RepositoryInterface<TicketType>
 */
final class TicketTypeRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = TicketType::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @param Concert $concert
     * @return TicketType[]
     */
    public function fetchByConcert(Concert $concert): array
    {
        return $this->fetchAll(['concertId = ?'], [$concert->id], 'ORDER BY id');
    }

    /**
     * @param Concert $concert
     * @return TicketType[]
     */
    public function fetchByConcertAndSortByPrice(Concert $concert): array
    {
        $ticketTypes = $this->fetchByConcert($concert);
        usort($ticketTypes, static function(TicketType $tt1, TicketType $tt2): int
        {
            return $tt2->price <=> $tt1->price;
        });
        return $ticketTypes;
    }
}
