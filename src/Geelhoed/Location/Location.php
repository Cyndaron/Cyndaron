<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Model;
use function urlencode;
use function implode;

final class Location extends Model
{
    public const TABLE = 'geelhoed_locations';
    public const TABLE_FIELDS = ['name', 'street', 'houseNumber', 'postalCode', 'city'];

    public string $name;
    public string $street;
    public string $houseNumber;
    public string $postalCode;
    public string $city;

    /**
     * @param int|null $departmentId
     * @return Hour[]
     */
    public function getHours(?int $departmentId = null): array
    {
        if ($departmentId !== null)
        {
            return Hour::fetchAll(['locationId = ?', 'departmentId = ?'], [$this->id, $departmentId], 'ORDER BY `day`, `from`');
        }

        return Hour::fetchAll(['locationId = ?'], [$this->id], 'ORDER BY `day`, `from`');
    }

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

    public function getName(): string
    {
        return $this->name ?: "$this->street $this->city";
    }

    public function getMapsLink(): string
    {
        $urlData = [$this->street, $this->houseNumber, $this->postalCode, $this->city];
        return 'https://www.google.nl/maps/place/' . urlencode(implode(' ', $urlData));
    }

    /**
     * Get all the cities where we have lessons.
     *
     * @return array
     */
    public static function getCities(): array
    {
        $ret = [];
        $results = DBConnection::doQueryAndFetchAll('SELECT DISTINCT city FROM geelhoed_locations ORDER BY city') ?: [];
        foreach ($results as $result)
        {
            $ret[] = $result['city'];
        }
        return $ret;
    }
}
