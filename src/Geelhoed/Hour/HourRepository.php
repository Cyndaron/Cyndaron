<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Geelhoed\Sport\Sport;

/**
 * @implements RepositoryInterface<Hour>
 */
final class HourRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Hour::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @param int $age
     * @param Sport $sport
     * @return Hour[]
     */
    public function fetchByAgeAndSport(int $age, Sport $sport): array
    {
        return $this->fetchAll(['minAge <= ?', '(maxAge IS NULL OR maxAge >= ?)', 'sportId = ?'], [$age, $age, $sport->id], 'ORDER BY locationId, day');
    }
}
