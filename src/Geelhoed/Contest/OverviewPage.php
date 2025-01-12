<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Page\Page;

final class OverviewPage extends Page
{
    public function __construct(ContestDateRepository $contestDateRepository)
    {
        $contests = Contest::fetchAllCurrentWithDate();
        $this->title = 'Overzicht wedstrijden';
        $this->addTemplateVars([
            'contestDateRepository' => $contestDateRepository,
            'contests' => $contests
        ]);
    }
}
