<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use function array_map;
use function count;
use function sprintf;
use function implode;

/**
 * @implements RepositoryInterface<ContestDate>
 */
class ContestDateRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = ContestDate::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly Connection $connection,
        private readonly ContestClassRepository $contestClassRepository,
    ) {
    }

    public function addClass(ContestDate $contestDate, ContestClass $class): void
    {
        $this->connection->insert('REPLACE INTO geelhoed_contests_dates_classes(`contestDateId`, `classId`) VALUES (?, ?)', [$contestDate->id, $class->id]);
    }

    /**
     * @param ContestDate $contestDate
     * @return ContestClass[]
     */
    public function getClasses(ContestDate $contestDate): array
    {
        return $this->contestClassRepository->fetchAll(['id IN (SELECT classId FROM geelhoed_contests_dates_classes WHERE contestDateId = ?)'], [$contestDate->id]);
    }

    public function delete(Model $model): void
    {
        $this->connection->executeQuery('DELETE FROM geelhoed_contests_dates_classes WHERE contestDateId = ?', [$model->id]);
        $this->genericRepository->delete($model);
    }

    public function getName(ContestDate $contestDate): string
    {
        $classNames = array_map(static fn ($class) => $class->name, $this->getClasses($contestDate));
        if (count($classNames) === 0)
        {
            return $contestDate->contest->name;
        }

        return sprintf('%s (%s)', $contestDate->contest->name, implode(', ', $classNames));
    }
}
