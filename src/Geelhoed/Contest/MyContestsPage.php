<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use function array_map;
use function count;
use function implode;

final class MyContestsPage extends Page
{
    public function __construct(User $currentUser, CSRFTokenHandler $tokenHandler)
    {
        parent::__construct('Mijn wedstrijden');
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $controlledMembers = Member::fetchAllContestantsByUser($currentUser);
        $contests = [];
        $contestMembers = [];
        $due = 0.0;
        if (count($controlledMembers) > 0)
        {
            $memberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $controlledMembers);
            $contests = Contest::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (' . implode(',', $memberIds) . '))'], [], 'ORDER BY registrationDeadline DESC');
            [$due, $contestMembers] = Contest::getTotalDue($currentUser);
        }
        $this->addScript('/src/Geelhoed/Contest/js/MemberSubscriptionStatus.js');
        $this->addTemplateVars([
            'profile' => $currentUser,
            'controlledMembers' => $controlledMembers,
            'contests' => $contests,
            'contestMembers' => $contestMembers,
            'due' => $due,
            'cancelSubscriptionCsrfToken' => $tokenHandler->get('contest', 'cancelSubscription'),
        ]);
    }
}
