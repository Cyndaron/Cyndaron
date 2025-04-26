<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Page\Page;

class SearchPage extends Page
{
    private const PAGE_IMAGE = '/src/Geelhoed/Location/images/location-overview.jpg';

    /**
     * @param array<int, string> $days
     */
    public function __construct(array $days, LocationRepository $locationRepository, SportRepository $sportRepository)
    {
        $this->title = 'Lessen zoeken';

        $this->addCss('/src/Geelhoed/geelhoed.css');
        $this->addScript('/src/Geelhoed/Location/js/SearchPage.js');

        $this->addTemplateVars([
            'pageImage' => self::PAGE_IMAGE,
            'cities' => $locationRepository->getCities(),
            'days' => $days,
            'sports' => $sportRepository->fetchAll(),
        ]);
    }
}
