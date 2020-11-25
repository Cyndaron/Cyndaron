<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use function assert;

final class ContestDate extends Model
{
    public const TABLE = 'geelhoed_contests_dates';
    public const TABLE_FIELDS = ['contestId', 'datetime'];

    public int $contestId;
    public string $datetime;

    /**
     * @param Contest $contest
     * @return self[]
     */
    public function fetchAllByContest(Contest $contest): array
    {
        return self::fetchAll(['contestId = ?'], [$contest->id]);
    }

    /**
     * @return ContestClass[]
     */
    public function getClasses(): array
    {
        return ContestClass::fetchAll(['id IN (SELECT classId FROM geelhoed_contests_dates_classes WHERE contestDateId = ?)'], [$this->id]);
    }

    public function addClass(ContestClass $class): bool
    {
        $result = DBConnection::doQuery('REPLACE INTO geelhoed_contests_dates_classes(`contestDateId`, `classId`) VALUES (?, ?)', [$this->id, $class->id]);
        return $result !== false;
    }

    public function getContest(): Contest
    {
        $contest = Contest::loadFromDatabase($this->contestId);
        assert($contest !== null);
        return $contest;
    }

    public function delete(): void
    {
        DBConnection::doQuery('DELETE FROM geelhoed_contests_dates_classes WHERE contestDateId = ?', [$this->id]);
        parent::delete();
    }
}
