<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\DBAL\CacheableModel;
use Cyndaron\Geelhoed\Department;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\View\Template\ViewHelpers;

use function sprintf;
use function assert;

final class Hour extends CacheableModel
{
    public const TABLE = 'geelhoed_hours';
    public const TABLE_FIELDS = ['locationId', 'day', 'description', 'from', 'until', 'sportId', 'sportOverride', 'departmentId', 'capacity', 'notes'];

    public int $locationId;
    public int $day;
    public string $description;
    public string $from;
    public string $until;
    public int $sportId;
    public string $sportOverride;
    public int $departmentId;
    public int $capacity;
    public string $notes;

    public function getLocation(): Location
    {
        $ret = Location::fetchById($this->locationId);
        assert($ret !== null);
        return $ret;
    }

    public function getSport(): Sport
    {
        $ret = Sport::fetchById((int)$this->sportId);
        assert($ret !== null);
        return $ret;
    }

    public function getSportName(): string
    {
        if ($this->sportOverride !== '')
        {
            return $this->sportOverride;
        }
        return $this->getSport()->name;
    }

    public function getDepartment(): Department
    {
        $ret = Department::fetchById((int)$this->departmentId);
        assert($ret !== null);
        return $ret;
    }

    public function getRange(): string
    {
        return sprintf('%s – %s', ViewHelpers::filterHm($this->from), ViewHelpers::filterHm($this->until));
    }

    /**
     * @param int $age
     * @return self[]
     */
    public static function fetchByAge(int $age): array
    {
        return self::fetchAll(['minAge <= ? AND (maxAge IS NULL OR maxAge >= ?)'], [$age, $age], 'ORDER BY locationId, day');
    }
}
