<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Page\Page;

final class ContestantsListPage extends Page
{
    public function __construct(MemberRepository $memberRepository, SportRepository $sportRepository)
    {
        $this->title = 'Overzicht wedstrijdjudoka\'s';
        $contestants = $memberRepository->fetchAllAndSortByName(['isContestant = 1']);
        $sports = $sportRepository->fetchAll();
        $this->addTemplateVars(['contestants' => $contestants, 'sports' => $sports]);
    }
}
