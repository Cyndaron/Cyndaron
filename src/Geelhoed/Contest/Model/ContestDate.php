<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\Calendar\CalendarAppointment;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use DateTime;
use function array_map;
use function count;
use function implode;
use function sprintf;

final class ContestDate extends Model implements CalendarAppointment
{
    public const TABLE = 'geelhoed_contests_dates';

    #[DatabaseField(dbName: 'contestId')]
    public Contest $contest;
    #[DatabaseField]
    public DateTime $start;
    #[DatabaseField]
    public DateTime $end;

    public function getName(): string
    {
        return $this->contest->name;
    }

    public function getDescription(): string
    {
        return $this->contest->description;
    }

    public function getLocation(): string
    {
        return $this->contest->location;
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
        return "/contest/view/{$this->contest->id}";
    }
}
