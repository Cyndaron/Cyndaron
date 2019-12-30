<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Model;

class Hour extends Model
{
    const TABLE = 'geelhoed_hours';
    const TABLE_FIELDS = ['locationId', 'day', 'description', 'from', 'until', 'sportId', 'sportOverride', 'notes'];

    public $locationId;
    public $day;
    public $description;
    public $from;
    public $until;
    public $sportId;
    public $sportOverride;
    public $notes;

    public function getLocation(): Location
    {
        return Location::loadFromDatabase((int)$this->locationId);
    }

    public function getSport(): Sport
    {
        return Sport::loadFromDatabase((int)$this->sportId);
    }

    public function getSportName(): string
    {
        if ($this->sportOverride)
        {
            return $this->sportOverride;
        }
        return $this->getSport()->name;
    }
}