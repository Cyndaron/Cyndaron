<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use PDO;

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
        return ContestClass::fetchAll(['id IN (SELECT classId FROM geelhoed_contests_dates_classes WHERE contestDateId = ?)'], [$contestDate->id]);
    }

    public function delete(Model $model): void
    {
        $this->connection->executeQuery('DELETE FROM geelhoed_contests_dates_classes WHERE contestDateId = ?', [$model->id]);
        $this->genericRepository->delete($model);
    }
}
