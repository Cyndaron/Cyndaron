<?php
declare(strict_types=1);

namespace Cyndaron\Location;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use function urlencode;
use function implode;

final class Location extends Model
{
    public const TABLE = 'locations';

    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public string $street;
    #[DatabaseField]
    public string $houseNumber;
    #[DatabaseField]
    public string $postalCode;
    #[DatabaseField]
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
