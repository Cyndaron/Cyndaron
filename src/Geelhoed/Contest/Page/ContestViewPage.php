<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestClassRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use Cyndaron\User\UserSession;

final class ContestViewPage
{
    private User|null $currentUser;

    public function __construct(
        UserSession $userSession,
        private readonly CSRFTokenHandler $tokenHandler,
        private readonly ContestRepository $contestRepository,
        private readonly ContestClassRepository $contestClassRepository,
        private readonly ContestDateRepository $contestDateRepository,
        private readonly ContestMemberRepository $contestMemberRepository,
        private readonly MemberRepository $memberRepository,
        private readonly UserRepository $userRepository
    ) {
        $this->currentUser = $userSession->getProfile();
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
     * @param ContestRepository $contestRepository
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

    public function createPage(Contest $contest): Page
    {
        $page = new Page();
        $page->template = 'Geelhoed/Contest/Page/ContestViewPage';
        $page->title = "Wedstrijd: {$contest->name}";
        $page->addCss('/src/Geelhoed/geelhoed.css');
        $page->addScript('/src/Geelhoed/Contest/js/ContestViewPage.js');
        $page->addScript('/src/Geelhoed/Contest/js/MemberSubscriptionStatus.js');

        $controlledMembers = $this->currentUser !== null ? $this->memberRepository->fetchAllContestantsByUser($this->currentUser) : [];
        $canManage = $this->currentUser !== null && $this->userRepository->userHasRight($this->currentUser, Contest::RIGHT_MANAGE);
        $mayViewOtherContestants = $this->loggedInUserMayViewOtherContestants($canManage, $controlledMembers);

        $page->addTemplateVars([
            'addAttachmentToken' => $this->tokenHandler->get('contest', 'addAttachment'),
            'addDateCsrfToken' => $this->tokenHandler->get('contest', 'addDate'),
            'allSubscribed' => $this->areAllSubscribed($this->contestRepository, $contest, $controlledMembers),
            'canManage' => $canManage,
            'cancelSubscriptionCsrfToken' => $this->tokenHandler->get('contest', 'cancelSubscription'),
            'contest' => $contest,
            'contestDateRepository' => $this->contestDateRepository,
            'contestMemberRepository' => $this->contestMemberRepository,
            'contestRepository' => $this->contestRepository,
            'controlledMembers' => $controlledMembers,
            'deleteCsrfToken' => $this->tokenHandler->get('contest', 'deleteAttachment'),
            'deleteDateCsrfToken' => $this->tokenHandler->get('contest', 'deleteDate'),
            'due' => $this->currentUser !== null ? $this->getTotalDue($this->contestRepository, $this->currentUser, $this->memberRepository) : 0.00,
            'mayViewOtherContestants' => $mayViewOtherContestants,
            'profile' => $this->currentUser,
            'contestClasses' => $this->contestClassRepository->fetchAll(),
            'memberRepository' => $this->memberRepository,
        ]);

        return $page;
    }
}
