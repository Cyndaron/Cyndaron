<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

class Hour extends Model
{
    const TABLE = 'geelhoed_hours';
    const TABLE_FIELDS = ['locationId', 'day', 'from', 'until', 'sport', 'notes'];

    public $locationId;
    public $day;
    public $from;
    public $until;
    public $sport;
    public $notes;

    public function getLocation(): Location
    {
        return Location::loadFromDatabase((int)$this->locationId);
    }
}