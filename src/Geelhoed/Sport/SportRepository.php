<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<Sport>
 */
final class SportRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Sport::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
