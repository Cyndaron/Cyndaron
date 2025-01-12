<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Page\Page;

final class ContestantsListPage extends Page
{
    public function __construct()
    {
        $this->title = 'Overzicht wedstrijdjudoka\'s';
        $contestants = Member::fetchAllAndSortByName(['isContestant = 1']);
        $sports = Sport::fetchAll();
        $this->addTemplateVars(['contestants' => $contestants, 'sports' => $sports]);
    }
}
