<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<Concert>
 */
final class ConcertRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Concert::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }
}
