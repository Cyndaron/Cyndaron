<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\User\Module\UserMenuItem;
use function array_filter;

final class UserMenu
{
    /**
     * @param User|null $currentUser
     * @param UserMenuItem[] $menuItems
     * @return UserMenuItem[]
     */
    public static function getForUser(User|null $currentUser, UserSession $userSession, array $menuItems): array
    {
        return array_filter($menuItems, static function(UserMenuItem $userMenuItem) use ($currentUser, $userSession)
        {
            $level = $userMenuItem->level;
            if ($userSession->getLevel() >= $level)
            {
                return true;
            }
            $right = $userMenuItem->right ?? '';
            if ($right !== '' && $currentUser !== null && $currentUser->hasRight($right))
            {
                return true;
            }

            return false;
        });
    }
}
