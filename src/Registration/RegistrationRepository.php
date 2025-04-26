<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<Registration>
 */
final class RegistrationRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Registration::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    /**
     * @param Event $event
     * @return Registration[]
     */
    public function loadByEvent(Event $event): array
    {
        return $this->fetchAll(['eventId = ?'], [$event->id], 'ORDER BY id');
    }
}
