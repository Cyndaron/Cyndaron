<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\View\Page;
use Cyndaron\User\User;
use function usort;

final class ParentAccountsPage extends Page
{
    public function __construct()
    {
        parent::__construct('Lijst ouderaccounts');

        $users = User::fetchAll(['id IN (SELECT `userId` FROM `user_rights` WHERE `right` = ?)'], [Contest::RIGHT_PARENT]);
        usort($users, static function(User $user1, User $user2)
        {
            return $user1->lastName <=> $user2->lastName;
        });
        $this->addScript('/src/Geelhoed/Contest/js/ParentAccountsManager.js');
        $this->addTemplateVars([
            'users' => $users,
        ]);
    }
}
