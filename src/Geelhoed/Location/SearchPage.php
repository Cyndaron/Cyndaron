<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Page\Page;
use Cyndaron\View\Template\ViewHelpers;

class SearchPage extends Page
{
    private const PAGE_IMAGE = '/src/Geelhoed/Location/images/location-overview.jpg';

    public function __construct()
    {
        parent::__construct('Lessen zoeken');

        $this->addCss('/src/Geelhoed/geelhoed.css');
        $this->addScript('/src/Geelhoed/Location/js/SearchPage.js');

        $dayRecords = DBConnection::doQueryAndFetchAll('SELECT DISTINCT(day) AS number FROM geelhoed_hours ORDER by number') ?: [];
        $days = [];
        foreach ($dayRecords as $dayRecord)
        {
            $number = (int)$dayRecord['number'];
            $days[$number] = ViewHelpers::getDutchWeekday($number);
        }

        $this->addTemplateVars([
            'pageImage' => self::PAGE_IMAGE,
            'cities' => Location::getCities(),
            'days' => $days,
            'sports' => Sport::fetchAll(),
        ]);
    }
}
