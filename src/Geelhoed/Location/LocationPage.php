<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Page;
use Cyndaron\Setting;

final class LocationPage extends Page
{
    public function __construct(Location $location)
    {
        parent::__construct($location->getName());
        $locNotification = Setting::get('geelhoed_locationNotification');
        $this->addTemplateVars(['location' => $location, 'locNotification' => $locNotification]);
    }
}
