<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use function array_filter;
use function usort;

final class LinkContestantsToParentAccountsPage extends Page
{
    public function __construct()
    {
        $this->title = 'Wedstrijdjudoka’s linken aan ouderaccounts';

        $contestants = array_filter(
            Member::fetchAllAndSortByName(['isContestant = 1']),
            static function(Member $contestant)
            {
                return $contestant->getProfile()->getAge() < 21;
            }
        );
        $parentsPerContestant = [];
        $parents = User::fetchAll(['id IN (SELECT `userId` FROM `user_rights` WHERE `right` = ?)'], [Contest::RIGHT_PARENT]);
        usort($parents, static function(User $user1, User $user2)
        {
            return $user1->lastName <=> $user2->lastName;
        });
        foreach ($parents as $parent)
        {
            $controlledMembers = Member::fetchAllByUser($parent);
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
