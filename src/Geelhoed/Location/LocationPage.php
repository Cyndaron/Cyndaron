<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Location\Location;
use Cyndaron\Page\Page;
use Cyndaron\Util\Setting;

final class LocationPage extends Page
{
    public function __construct(Location $location)
    {
        $this->title = $location->getName();
        $locNotification = Setting::get('geelhoed_locationNotification');
        $this->addTemplateVars(['location' => $location, 'locNotification' => $locNotification]);
    }
}
