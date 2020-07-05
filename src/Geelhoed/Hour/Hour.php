<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\Geelhoed\Department;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\Model;
use Cyndaron\Template\ViewHelpers;

use function Safe\sprintf;

final class Hour extends Model
{
    public const TABLE = 'geelhoed_hours';
    public const TABLE_FIELDS = ['locationId', 'day', 'description', 'from', 'until', 'sportId', 'sportOverride', 'departmentId', 'notes'];

    public int $locationId;
    public int $day;
    public string $description;
    public string $from;
    public string $until;
    public int $sportId;
    public string $sportOverride;
    public int $departmentId;
    public string $notes;

    /** @var Hour[] */
    private static array $cache = [];

    public function getLocation(): Location
    {
        return Location::loadFromDatabase((int)$this->locationId);
    }

    public function getSport(): Sport
    {
        return Sport::loadFromDatabase((int)$this->sportId);
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
        return Department::loadFromDatabase((int)$this->departmentId);
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
    public static function loadFromDatabase(int $id): ?Model
    {
        if (array_key_exists($id, static::$cache))
        {
            return static::$cache[$id];
        }

        /** @var static $object */
        $object = parent::loadFromDatabase($id);
        static::$cache[$id] = $object;

        return $object;
    }

    public function save(): bool
    {
        $result = parent::save();
        if ($result)
        {
            static::$cache[$this->id] = $this;
        }
        return $result;
    }
}
