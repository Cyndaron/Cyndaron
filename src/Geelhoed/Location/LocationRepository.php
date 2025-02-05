<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Location\Location;
use function array_unique;
use function array_filter;
use function usort;

/**
 * @implements RepositoryInterface<Location>
 */
final class LocationRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Location::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly HourRepository $hourRepository,
    ) {
    }

    /**
     * @param Location $location
     * @param int|null $departmentId
     * @return Hour[]
     */
    public function getHours(Location $location, int|null $departmentId = null): array
    {
        $hours = $this->hourRepository->fetchAll();
        $filtered = array_filter($hours, function(Hour $hour) use ($location, $departmentId)
        {
            if ($hour->location->id !== $location->id)
            {
                return false;
            }

            if ($departmentId !== null && $hour->department->id !== $departmentId)
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
    public function getHoursSortedByWeekday(Location $location): array
    {
        $ret = [];
        $hours = $this->getHours($location);

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
    public function getCities(): array
    {
        $ret = [];
        foreach ($this->getWithLessions() as $result)
        {
            $ret[] = $result->city;
        }
        return array_unique($ret);
    }

    /**
     * @return Location[]
     */
    public function getWithLessions(): array
    {
        return $this->fetchAll(['id IN (SELECT locationId FROM geelhoed_hours)'], [], 'ORDER BY city, street');

    }
}
