<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use function array_map;
use function count;
use function implode;

final class MyContestsPage
{
    public function __construct(
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly ContestRepository $contestRepository,
        private readonly ContestDateRepository $contestDateRepository,
        private readonly ContestMemberRepository $contestMemberRepository,
        private readonly MemberRepository $memberRepository,
        private readonly User $currentUser,
    ) {
    }

    public function createPage(): Page
    {
        $page = new Page();
        $page->title = 'Mijn wedstrijden';
        $page->template = 'Geelhoed/Contest/Page/MyContestsPage';
        $page->addCss('/src/Geelhoed/geelhoed.css');
        $page->addScript('/src/Geelhoed/Contest/js/MemberSubscriptionStatus.js');

        $controlledMembers = $this->memberRepository->fetchAllContestantsByUser($this->currentUser);
        $contests = [];
        $contestMembers = [];
        $due = 0.0;
        if (count($controlledMembers) > 0)
        {
            $memberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $controlledMembers);
            $contests = $this->contestRepository->fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (' . implode(',', $memberIds) . '))'], [], 'ORDER BY registrationDeadline DESC');
            [$due, $contestMembers] = $this->contestRepository->getTotalDue($this->currentUser, $this->memberRepository);
        }

        $page->addTemplateVars([
            'profile' => $this->currentUser,
            'controlledMembers' => $controlledMembers,
            'contests' => $contests,
            'contestRepository' => $this->contestRepository,
            'contestDateRepository' => $this->contestDateRepository,
            'contestMemberRepository' => $this->contestMemberRepository,
            'contestMembers' => $contestMembers,
            'due' => $due,
            'cancelSubscriptionCsrfToken' => $this->tokenHandler->get('contest', 'cancelSubscription'),
            'memberRepository' => $this->memberRepository,
        ]);
        return $page;
    }
}
