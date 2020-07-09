<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page;
use Cyndaron\User\User;

final class MyContestsPage extends Page
{
    public function __construct()
    {
        parent::__construct('Overzicht wedstrijdjudoka\'s');
        $user = User::getLoggedIn();
        $loggedInMember = Member::loadFromLoggedInUser();
        $contests = [];
        $due = 0.0;
        if ($loggedInMember !== null)
        {
            $contests = Contest::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId = ?)'], [$loggedInMember->id], 'ORDER BY date DESC');
            $due = $this->calculateDue($loggedInMember, $contests);
        }
        $this->addTemplateVars([
            'profile' => $user,
            'member' => $loggedInMember,
            'contests' => $contests,
            'due' => $due,
        ]);
    }

    /**
     * @param Contest[] $contests
     * @return float
     */
    private function calculateDue(Member $member, array $contests): float
    {
        $due = 0.0;

        foreach ($contests as $contest)
        {
            $contestMember = ContestMember::fetchByContestAndMember($contest, $member);
            if ($contestMember !== null && !$contestMember->isPaid)
            {
                $due += $contest->price;
            }
        }

        return $due;
    }
}
