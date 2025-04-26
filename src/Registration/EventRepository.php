<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<Event>
 */
final class EventRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Event::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }
}
