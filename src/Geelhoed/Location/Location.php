<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Department;
use Cyndaron\Geelhoed\Hour;
use Cyndaron\Model;

class Location extends Model
{
    const TABLE = 'geelhoed_locations';
    const TABLE_FIELDS = ['name', 'street', 'houseNumber', 'postalCode', 'city'];

    public $name;
    public $street;
    public $houseNumber;
    public $postalCode;
    public $city;

    /**
     * @return Hour[]
     */
    public function getHours(): array
    {
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