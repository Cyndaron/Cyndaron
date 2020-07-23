<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page;
use Cyndaron\User\User;

use function Safe\sprintf;

final class ContestViewPage extends Page
{
    public function __construct(Contest $contest)
    {
        $user = User::getLoggedIn();
        $controlledMembers = Member::fetchAllContestantsByLoggedInUser();
        $canManage = $user !== null && $user->hasRight(Contest::RIGHT_MANAGE);
        $mayViewOtherContestants = $this->loggedInUserMayViewOtherContestants($canManage, $controlledMembers);
        parent::__construct(sprintf('Wedstrijd: %s', $contest->name));
        $this->addTemplateVars([
            'contest' => $contest,
            'controlledMembers' => $controlledMembers,
            'allSubscribed' => $this->areAllSubscribed($contest, $controlledMembers),
            'mayViewOtherContestants' => $mayViewOtherContestants,
            'canManage' => $canManage,
            'deleteCsrfToken' => User::getCSRFToken('contest', 'deleteAttachment'),
            'addDateCsrfToken' => User::getCSRFToken('contest', 'addDate'),
            'deleteDateCsrfToken' => User::getCSRFToken('contest', 'deleteDate'),
            'due' => $user !== null ? $this->getTotalDue($user) : 0.00,
        ]);
        $this->addScript('/src/Geelhoed/Contest/js/ContestViewPage.js');
    }

    private function loggedInUserMayViewOtherContestants(bool $currentUserCanManageContests, array $controlledMembers): bool
    {
        if ($currentUserCanManageContests)
        {
            return true;
        }

        foreach ($controlledMembers as $controlledMember)
        {
            if ($controlledMember->isContestant)
            {
                return true;
            }
        }

        return false;
    }

    private function areAllSubscribed(Contest $contest, array $controlledMembers): bool
    {
        foreach ($controlledMembers as $controlledMember)
        {
            if (!$contest->hasMember($controlledMember, true))
            {
                return false;
            }
        }

        return true;
    }

    private function getTotalDue(User $user): float
    {
        [$due, $contestMembers] = Contest::getTotalDue($user);
        return $due;
    }
}
