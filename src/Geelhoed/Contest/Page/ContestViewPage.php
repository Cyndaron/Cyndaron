<?php
namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;

final class ContestViewPage extends Page
{
    public function __construct(Contest $contest, User|null $currentUser, CSRFTokenHandler $tokenHandler, ContestRepository $contestRepository, ContestDateRepository $contestDateRepository, MemberRepository $memberRepository)
    {
        $controlledMembers = $currentUser !== null ? $memberRepository->fetchAllContestantsByUser($currentUser) : [];
        $canManage = $currentUser !== null && $currentUser->hasRight(Contest::RIGHT_MANAGE);
        $mayViewOtherContestants = $this->loggedInUserMayViewOtherContestants($canManage, $controlledMembers);
        $this->title = "Wedstrijd: {$contest->name}";
        $this->addCss('/src/Geelhoed/geelhoed.css');
        $this->addTemplateVars([
            'addAttachmentToken' => $tokenHandler->get('contest', 'addAttachment'),
            'addDateCsrfToken' => $tokenHandler->get('contest', 'addDate'),
            'allSubscribed' => $this->areAllSubscribed($contestRepository, $contest, $controlledMembers),
            'canManage' => $canManage,
            'cancelSubscriptionCsrfToken' => $tokenHandler->get('contest', 'cancelSubscription'),
            'contest' => $contest,
            'contestDateRepository' => $contestDateRepository,
            'controlledMembers' => $controlledMembers,
            'deleteCsrfToken' => $tokenHandler->get('contest', 'deleteAttachment'),
            'deleteDateCsrfToken' => $tokenHandler->get('contest', 'deleteDate'),
            'due' => $currentUser !== null ? $this->getTotalDue($contestRepository, $currentUser, $memberRepository) : 0.00,
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
    private function areAllSubscribed(ContestRepository $contestRepository, Contest $contest, array $controlledMembers): bool
    {
        foreach ($controlledMembers as $controlledMember)
        {
            if (!$contestRepository->hasMember($contest, $controlledMember, true))
            {
                return false;
            }
        }

        return true;
    }

    private function getTotalDue(ContestRepository $contestRepository, User $user, MemberRepository $memberRepository): float
    {
        [$due, $contestMembers] = $contestRepository->getTotalDue($user, $memberRepository);
        return $due;
    }
}
