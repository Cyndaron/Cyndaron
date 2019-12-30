<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Department;
use Cyndaron\Geelhoed\Hour;
use Cyndaron\Model;

class Location extends Model
{
    const TABLE = 'geelhoed_locations';
    const TABLE_FIELDS = ['name', 'street', 'houseNumber', 'postalCode', 'city', 'departmentId'];

    public $name;
    public $street;
    public $houseNumber;
    public $postalCode;
    public $city;
    public $departmentId;

    /**
     * @return Hour[]
     */
    public function getHours(): array
    {
        return Hour::fetchAll(['locationId = ?'], [$this->id], 'ORDER BY `from`');
    }

    public function getName(): string
    {
        return $this->name ?: "$this->street $this->city";
    }

    public function getDepartment(): Department
    {
        return Department::loadFromDatabase((int)$this->departmentId);
    }

    public function getMapsLink(): string
    {
        $urlData = [$this->street, $this->houseNumber, $this->postalCode, $this->city];
        return 'https://www.google.nl/maps/place/' . urlencode(implode(' ', $urlData));
    }
}