<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\View\Page;
use Cyndaron\Util\Setting;

final class LocationPage extends Page
{
    public function __construct(Location $location)
    {
        parent::__construct($location->getName());
        $locNotification = Setting::get('geelhoed_locationNotification');
        $this->addTemplateVars(['location' => $location, 'locNotification' => $locNotification]);
    }
}
