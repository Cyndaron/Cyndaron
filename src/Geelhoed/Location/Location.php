<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Hour\Hour;
use function array_unique;
use function urlencode;
use function implode;

final class Location
{
    public function __construct(public readonly \Cyndaron\Location\Location $base)
    {
    }

    /**
     * @param int|null $departmentId
     * @return Hour[]
     */
    public function getHours(int|null $departmentId = null): array
    {
        if ($departmentId !== null)
        {
            return Hour::fetchAll(['locationId = ?', 'departmentId = ?'], [$this->base->id, $departmentId], 'ORDER BY `day`, `from`');
        }

        return Hour::fetchAll(['locationId = ?'], [$this->base->id], 'ORDER BY `day`, `from`');
    }

    /**
     * @return array<int, Hour[]>
     */
    public function getHoursSortedByWeekday(): array
    {
        $ret = [];
        $hours = $this->getHours();

        foreach ($hours as $hour)
        {
            if (empty($ret[$hour->day]))
            {
                $ret[$hour->day] = [];
            }

            $ret[$hour->day][] = $hour;
        }

        return $ret;
    }

    /**
     * Get all the cities where we have lessons.
     *
     * @return string[]
     */
    public static function getCities(): array
    {
        $ret = [];
        $results = \Cyndaron\Location\Location::fetchAll(['id IN (SELECT locationId FROM geelhoed_hours)'], [], 'ORDER BY city');
        foreach ($results as $result)
        {
            $ret[] = $result->city;
        }
        return array_unique($ret);
    }
}
