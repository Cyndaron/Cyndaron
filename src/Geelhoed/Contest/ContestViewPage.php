<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use function sprintf;

final class ContestViewPage extends Page
{
    public function __construct(Contest $contest, ?User $currentUser)
    {
        $controlledMembers = $currentUser !== null ? Member::fetchAllContestantsByUser($currentUser) : [];
        $canManage = $currentUser !== null && $currentUser->hasRight(Contest::RIGHT_MANAGE);
        $mayViewOtherContestants = $this->loggedInUserMayViewOtherContestants($canManage, $controlledMembers);
        parent::__construct(sprintf('Wedstrijd: %s', $contest->name));
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $this->addTemplateVars([
            'addDateCsrfToken' => User::getCSRFToken('contest', 'addDate'),
            'allSubscribed' => $this->areAllSubscribed($contest, $controlledMembers),
            'canManage' => $canManage,
            'cancelSubscriptionCsrfToken' => User::getCSRFToken('contest', 'cancelSubscription'),
            'contest' => $contest,
            'controlledMembers' => $controlledMembers,
            'deleteCsrfToken' => User::getCSRFToken('contest', 'deleteAttachment'),
            'deleteDateCsrfToken' => User::getCSRFToken('contest', 'deleteDate'),
            'due' => $currentUser !== null ? $this->getTotalDue($currentUser) : 0.00,
            'mayViewOtherContestants' => $mayViewOtherContestants,
            'profile' => $currentUser,
        ]);
        $this->addScript('/src/Geelhoed/Contest/js/ContestViewPage.js');
        $this->addScript('/src/Geelhoed/Contest/js/MemberSubscriptionStatus.js');
    }

    /**
     * @param bool $currentUserCanManageContests
     * @param Member[] $controlledMembers
     * @return bool
     */
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

    /**
     * @param Contest $contest
     * @param Member[] $controlledMembers
     * @return bool
     */
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
