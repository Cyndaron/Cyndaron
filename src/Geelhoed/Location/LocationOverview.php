<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Page;

class LocationOverview extends Page
{
    public function __construct()
    {
        parent::__construct('Leslocaties');
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $locations = Location::fetchAll([], [], 'ORDER BY city, street');
        $pageImage = '/src/Geelhoed/Location/images/location-overview.jpg';
        $this->addTemplateVars(compact('locations', 'pageImage'));
    }
}
