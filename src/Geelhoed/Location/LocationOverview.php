<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Page;

class LocationOverview extends Page
{
    public function __construct()
    {
        parent::__construct('Leslocaties');
        $locations = Location::fetchAll([], [], 'ORDER BY city');
        $this->render(compact('locations'));
    }
}