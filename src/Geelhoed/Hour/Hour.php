<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Department;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\DBAL\Model;
use Cyndaron\View\Template\ViewHelpers;

use function sprintf;
use function assert;
use function array_key_exists;

final class Hour extends Model
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

    /** @var Hour[] */
    private static array $cache = [];

    public function getLocation(): Location
    {
        $ret = Location::loadFromDatabase($this->locationId);
        assert($ret !== null);
        return $ret;
    }

    public function getSport(): Sport
    {
        $ret = Sport::loadFromDatabase((int)$this->sportId);
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
        $ret = Department::loadFromDatabase((int)$this->departmentId);
        assert($ret !== null);
        return $ret;
    }

    public function getRange(): string
    {
        return sprintf('%s – %s', ViewHelpers::filterHm($this->from), ViewHelpers::filterHm($this->until));
    }

    /**
     * @param int $id
     * @throws \Exception
     * @return Hour|null
     */
    public static function loadFromDatabase(int $id): ?Hour
    {
        if (array_key_exists($id, self::$cache))
        {
            return self::$cache[$id];
        }

        /** @var static $object */
        $object = parent::loadFromDatabase($id);
        self::$cache[$id] = $object;

        return $object;
    }

    /**
     * @param int $age
     * @return self[]
     */
    public static function fetchByAge(int $age): array
    {
        return self::fetchAll(['minAge <= ? AND (maxAge IS NULL OR maxAge >= ?)'], [$age, $age], 'ORDER BY locationId, day');
    }

    public function save(): bool
    {
        $result = parent::save();
        if ($result)
        {
            self::$cache[$this->id] = $this;
        }
        return $result;
    }
}
