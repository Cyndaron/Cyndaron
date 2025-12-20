<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Location\Location;
use Cyndaron\Page\Page;
use Cyndaron\Util\Setting;

final class LocationPage extends Page
{
    public function __construct(Location $location, string $locNotification, LocationRepository $locationRepository)
    {
        $this->title = $location->getName();
        $this->addTemplateVars([
            'location' => $location,
            'locNotification' => $locNotification,
            'locationRepository' => $locationRepository,
        ]);
    }
}
