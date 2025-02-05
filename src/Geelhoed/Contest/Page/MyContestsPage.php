<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use function array_map;
use function count;
use function implode;

final class MyContestsPage extends Page
{
    public function __construct(User $currentUser, CSRFTokenHandler $tokenHandler, ContestRepository $contestRepository, ContestDateRepository $contestDateRepository, MemberRepository $memberRepository)
    {
        $this->title = 'Mijn wedstrijden';
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $controlledMembers = $memberRepository->fetchAllContestantsByUser($currentUser);
        $contests = [];
        $contestMembers = [];
        $due = 0.0;
        if (count($controlledMembers) > 0)
        {
            $memberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $controlledMembers);
            $contests = $contestRepository->fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (' . implode(',', $memberIds) . '))'], [], 'ORDER BY registrationDeadline DESC');
            [$due, $contestMembers] = $contestRepository->getTotalDue($currentUser, $memberRepository);
        }
        $this->addScript('/src/Geelhoed/Contest/js/MemberSubscriptionStatus.js');
        $this->addTemplateVars([
            'profile' => $currentUser,
            'controlledMembers' => $controlledMembers,
            'contests' => $contests,
            'contestDateRepository' => $contestDateRepository,
            'contestMembers' => $contestMembers,
            'due' => $due,
            'cancelSubscriptionCsrfToken' => $tokenHandler->get('contest', 'cancelSubscription'),
        ]);
    }
}
