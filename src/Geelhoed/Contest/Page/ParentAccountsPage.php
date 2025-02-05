<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Page;

use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use function usort;

final class ParentAccountsPage extends Page
{
    public function __construct(UserRepository $userRepository, MemberRepository $memberRepository)
    {
        $this->title = 'Lijst ouderaccounts';

        $users = $userRepository->fetchAll(['id IN (SELECT `userId` FROM `user_rights` WHERE `right` = ?)'], [Contest::RIGHT_PARENT]);
        usort($users, static function(User $user1, User $user2)
        {
            return $user1->lastName <=> $user2->lastName;
        });
        $this->addScript('/src/Geelhoed/Contest/js/ParentAccountsManager.js');
        $this->addTemplateVars([
            'users' => $users,
            'contestants' => $memberRepository->fetchAllAndSortByName(['isContestant = 1']),
            'memberRepository' => $memberRepository,
        ]);
    }
}
