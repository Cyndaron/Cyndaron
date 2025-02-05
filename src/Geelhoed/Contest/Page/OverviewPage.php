<?php
namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Page\Page;

final class OverviewPage extends Page
{
    public function __construct(ContestRepository $contestRepository, ContestDateRepository $contestDateRepository)
    {
        $contests = $contestRepository->fetchAllCurrentWithDate();
        $this->title = 'Overzicht wedstrijden';
        $this->addTemplateVars([
            'contestDateRepository' => $contestDateRepository,
            'contests' => $contests
        ]);
    }
}
