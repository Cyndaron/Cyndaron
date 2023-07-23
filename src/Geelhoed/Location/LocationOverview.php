<?php
namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Util\Util;
use Cyndaron\View\Page;
use Cyndaron\Util\Setting;
use Cyndaron\View\Template\ViewHelpers;
use function array_filter;

final class LocationOverview extends Page
{
    private const PAGE_IMAGE = '/src/Geelhoed/Location/images/location-overview.jpg';

    public function __construct(
        LocationFilter $filter = LocationFilter::NONE,
        string $filterValue = '',
    ) {
        $title = 'Leslocaties';

        $where = [];
        $args = [];
        if ($filter === LocationFilter::DAY)
        {
            $day = (int)$filterValue;
            $readable = ViewHelpers::getDutchWeekday($day);

            $title = "Locaties met les op {$readable}";
            $where = ['id IN (SELECT locationId from geelhoed_hours WHERE day = ?)'];
            $args = [$day];
        }

        $locations = Location::fetchAll($where, $args, 'ORDER BY city, street');
        if ($filter === LocationFilter::CITY)
        {
            $locations = array_filter($locations, static function(Location $location) use ($filterValue, &$title)
            {
                $match = Util::getSlug($location->city) === $filterValue;
                if ($match)
                {
                    $title = "Leslocaties in {$location->city}";
                }
                return $match;
            });
        }

        parent::__construct($title);
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $locNotification = Setting::get('geelhoed_locationNotification');
        $this->addTemplateVars(['locations' => $locations, 'pageImage' => self::PAGE_IMAGE, 'locNotification' => $locNotification]);
    }
}
