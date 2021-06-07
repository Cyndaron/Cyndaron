<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\View\Page;
use Cyndaron\User\User;
use function count;
use function assert;
use function array_map;
use function implode;

final class MyContestsPage extends Page
{
    public function __construct()
    {
        parent::__construct('Mijn wedstrijden');
        $user = User::getLoggedIn();
        $controlledMembers = Member::fetchAllContestantsByLoggedInUser();
        $contests = [];
        $contestMembers = [];
        $due = 0.0;
        if (count($controlledMembers) > 0)
        {
            assert($user !== null);
            $memberIds = array_map(static function(Member $member) { return $member->id; }, $controlledMembers);
            $contests = Contest::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (?))'], [implode(',', $memberIds)], 'ORDER BY registrationDeadline DESC');
            [$due, $contestMembers] = Contest::getTotalDue($user);
        }
        $this->addTemplateVars([
            'profile' => $user,
            'controlledMembers' => $controlledMembers,
            'contests' => $contests,
            'contestMembers' => $contestMembers,
            'due' => $due,
        ]);
    }
}
