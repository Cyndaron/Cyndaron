<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use DateTimeInterface;
use function array_map;
use function count;
use function implode;
use function reset;
use function sprintf;

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

    public function getFirstByContest(Contest $contest): DateTimeInterface|null
    {
        $dates = $this->fetchAllByContest($contest);
        if (count($dates) === 0)
        {
            return null;
        }

        return reset($dates)->start;
    }

    /**
     * @return ContestDate[]
     */
    public function fetchAllByContest(Contest $contest): array
    {
        return $this->fetchAll(['contestId = ?'], [$contest->id], 'ORDER BY start');
    }
}
