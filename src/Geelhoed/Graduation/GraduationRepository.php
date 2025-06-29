<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Graduation;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Geelhoed\Sport\Sport;

/**
 * @implements RepositoryInterface<Graduation>
 */
final class GraduationRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Graduation::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    /**
     * @param Sport $sport
     * @return Graduation[]
     */
    public function fetchAllBySport(Sport $sport): array
    {
        return $this->fetchAll(['sportId = ?'], [$sport->id]);
    }
}
