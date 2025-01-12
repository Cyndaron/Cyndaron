<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Location\Location as BaseLocation;
use function array_unique;
use function array_filter;
use function usort;

final class Location
{
    public function __construct(public readonly BaseLocation $base)
    {
    }

    /**
     * @param int|null $departmentId
     * @return Hour[]
     */
    public function getHours(int|null $departmentId = null): array
    {
        $hours = Hour::fetchAll();
        $filtered = array_filter($hours, function(Hour $hour) use ($departmentId)
        {
            if ($hour->location->id !== $this->base->id)
            {
                return false;
            }

            if ($departmentId !== null && $hour->departmentId !== $departmentId)
            {
                return false;
            }

            return true;
        });
        usort($filtered, static function(Hour $hour1, Hour $hour2)
        {
            $daySort = $hour1->day <=> $hour2->day;
            if ($daySort !== 0)
            {
                return $daySort;
            }

            return $hour1->from <=> $hour2->from;
        });
        return $filtered;
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
        foreach (self::getWithLessions() as $result)
        {
            $ret[] = $result->city;
        }
        return array_unique($ret);
    }

    /**
     * @return BaseLocation[]
     */
    public static function getWithLessions(): array
    {
        return BaseLocation::fetchAll(['id IN (SELECT locationId FROM geelhoed_hours)'], [], 'ORDER BY city, street');

    }
}
