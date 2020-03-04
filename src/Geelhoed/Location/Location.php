<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Hour;
use Cyndaron\Model;

class Location extends Model
{
    const TABLE = 'geelhoed_locations';
    const TABLE_FIELDS = ['name', 'street', 'houseNumber', 'postalCode', 'city'];

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
            return Hour::fetchAll(['locationId = ?', 'departmentId = ?'], [$this->id, $departmentId], 'ORDER BY `day`, `from`');

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
}