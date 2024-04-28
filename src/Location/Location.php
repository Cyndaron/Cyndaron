<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Hour\Hour;
use function array_unique;
use function urlencode;
use function implode;

final class Location extends Model
{
    use FileCachedModel;

    public const TABLE = 'locations';
    public const TABLE_FIELDS = ['name', 'street', 'houseNumber', 'postalCode', 'city'];

    public string $name;
    public string $street;
    public string $houseNumber;
    public string $postalCode;
    public string $city;

    public function getName(): string
    {
        if ($this->name)
        {
            return "{$this->name}, {$this->city}";
        }

        return "{$this->street}, {$this->city}";
    }

    public function getMapsLink(): string
    {
        $urlData = [$this->street, $this->houseNumber, $this->postalCode, $this->city];
        return 'https://www.google.nl/maps/place/' . urlencode(implode(' ', $urlData));
    }
}
