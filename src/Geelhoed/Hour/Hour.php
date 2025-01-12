<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Hour;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Department;
use Cyndaron\Location\Location;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\View\Template\ViewHelpers;
use function assert;
use function sprintf;

final class Hour extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_hours';

    #[DatabaseField('locationId')]
    public Location $location;
    #[DatabaseField]
    public int $day;
    #[DatabaseField]
    public string $description;
    #[DatabaseField]
    public string $from;
    #[DatabaseField]
    public string $until;
    #[DatabaseField('sportId')]
    public Sport $sport;
    #[DatabaseField]
    public string $sportOverride;
    #[DatabaseField]
    public int $departmentId;
    #[DatabaseField]
    public int $capacity;
    #[DatabaseField]
    public string $notes;

    public function getSportName(): string
    {
        if ($this->sportOverride !== '')
        {
            return $this->sportOverride;
        }
        return $this->sport->name;
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
     * @param Sport $sport
     * @return self[]
     */
    public static function fetchByAgeAndSport(int $age, Sport $sport): array
    {
        return self::fetchAll(['minAge <= ?', '(maxAge IS NULL OR maxAge >= ?)', 'sportId = ?'], [$age, $age, $sport->id], 'ORDER BY locationId, day');
    }
}
