<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Location;

use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Page\Page;

class SearchResultsByAgePage extends Page
{
    public function __construct(int $age, Sport $sport, HourRepository $hourRepository)
    {
        $this->title = 'Lessen zoeken';

        $this->addTemplateVars([
            'age' => $age,
            'hours' => $hourRepository->fetchByAgeAndSport($age, $sport),
            'sport' => $sport,
        ]);
    }
}
