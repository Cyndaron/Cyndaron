<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Page;
use Cyndaron\Setting;

final class LocationOverview extends Page
{
    private const PAGE_IMAGE = '/src/Geelhoed/Location/images/location-overview.jpg';

    public function __construct()
    {
        parent::__construct('Leslocaties');
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $locations = Location::fetchAll([], [], 'ORDER BY city, street');
        $locNotification = Setting::get('geelhoed_locationNotification');
        $this->addTemplateVars(['locations' => $locations, 'pageImage' => self::PAGE_IMAGE, 'locNotification' => $locNotification]);
    }
}
