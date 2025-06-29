<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<EventTicketType>
 */
final class EventTicketTypeRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = EventTicketType::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    /**
     * @param Event $event
     * @return EventTicketType[]
     */
    public function loadByEvent(Event $event): array
    {
        return $this->fetchAll(['eventId = ?'], [$event->id], 'ORDER BY price DESC');
    }
}
