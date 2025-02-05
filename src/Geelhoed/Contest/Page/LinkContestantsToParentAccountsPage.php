<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use function array_filter;
use function usort;

final class LinkContestantsToParentAccountsPage extends Page
{
    public function __construct(MemberRepository $memberRepository, UserRepository $userRepository)
    {
        $this->title = 'Wedstrijdjudoka’s linken aan ouderaccounts';

        $contestants = array_filter(
            $memberRepository->fetchAllAndSortByName(['isContestant = 1']),
            static function(Member $contestant)
            {
                return $contestant->profile->getAge() < 21;
            }
        );
        $parentsPerContestant = [];
        $parents = $userRepository->fetchAll(['id IN (SELECT `userId` FROM `user_rights` WHERE `right` = ?)'], [Contest::RIGHT_PARENT]);
        usort($parents, static function(User $user1, User $user2)
        {
            return $user1->lastName <=> $user2->lastName;
        });
        foreach ($parents as $parent)
        {
            $controlledMembers = $memberRepository->fetchAllByUser($parent);
            foreach ($controlledMembers as $controlledMember)
            {
                $parentsPerContestant[$controlledMember->id][] = $parent;
            }
        }
        $this->addScript('/src/Geelhoed/Contest/js/ParentAccountsManager.js');
        $this->addTemplateVars([
            'contestants' => $contestants,
            'parentsPerContestant' => $parentsPerContestant,
            'parents' => $parents,
        ]);
    }
}
