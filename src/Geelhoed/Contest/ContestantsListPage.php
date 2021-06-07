<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\View\Page;

final class ContestantsListPage extends Page
{
    public function __construct()
    {
        parent::__construct('Overzicht wedstrijdjudoka\'s');
        $contestants = Member::fetchAll(['isContestant = 1'], [], 'ORDER BY lastName,tussenvoegsel,firstName');
        $sports = Sport::fetchAll();
        $this->addTemplateVars(['contestants' => $contestants, 'sports' => $sports]);
    }
}
