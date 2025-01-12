<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Calendar\CalendarAppointment;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use DateTime;
use function assert;
use function array_map;
use function count;
use function sprintf;
use function implode;

final class ContestDate extends Model implements CalendarAppointment
{
    public const TABLE = 'geelhoed_contests_dates';
    public const TABLE_FIELDS = ['contestId', 'start', 'end'];

    public int $contestId;
    public DateTime $start;
    public DateTime $end;

    /**
     * @return ContestClass[]
     */
    public function getClasses(): array
    {
        return ContestClass::fetchAll(['id IN (SELECT classId FROM geelhoed_contests_dates_classes WHERE contestDateId = ?)'], [$this->id]);
    }

    public function getContest(): Contest
    {
        $contest = Contest::fetchById($this->contestId);
        assert($contest !== null);
        return $contest;
    }

    public function delete(): void
    {
        DBConnection::getPDO()->executeQuery('DELETE FROM geelhoed_contests_dates_classes WHERE contestDateId = ?', [$this->id]);
        parent::delete();
    }

    public function getName(): string
    {
        $classNames = array_map(static fn ($class) => $class->name, $this->getClasses());
        if (count($classNames) === 0)
        {
            return $this->getContest()->name;
        }

        return sprintf('%s (%s)', $this->getContest()->name, implode(', ', $classNames));
    }

    public function getDescription(): string
    {
        return $this->getContest()->description;
    }

    public function getLocation(): string
    {
        return $this->getContest()->location;
    }

    public function getStart(): \DateTimeInterface
    {
        return $this->start;
    }

    public function getEnd(): \DateTimeInterface
    {
        return $this->end;
    }

    public function getUrl(): string|null
    {
        return "/contest/view/{$this->getContest()->id}";
    }
}
