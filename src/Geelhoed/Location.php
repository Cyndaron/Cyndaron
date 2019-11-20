<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

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
        return Hour::fetchAll(['locationId = ?'], [$this->id], 'ORDER BY `from`');
    }

    public function getName(): string
    {
        return $this->name ?: "$this->street $this->city";
    }
}