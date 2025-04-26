<?php
namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;

final class SubscribePage extends Page
{
    public function __construct(Contest $contest, Member $member, GraduationRepository $graduationRepository, MemberRepository $memberRepository)
    {
        $this->title = "Inschrijven: {$contest->name}";
        $this->addTemplateVars([
            'contest' => $contest,
            'member' => $member,
            'graduations' => $graduationRepository->fetchAllBySport($contest->sport),
            'memberRepository' => $memberRepository,
        ]);
    }
}
