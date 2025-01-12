<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\Util\DependencyInjectionContainer;
use function array_filter;

final class UserMenu
{
    /**
     * @param UserRepository $repository
     * @param UserSession $userSession
     * @param UserMenuItem[] $menuItems
     * @return UserMenuItem[]
     */
    public static function getForCurrentSession(DependencyInjectionContainer $dic, UserRepository $repository, UserSession $userSession, array $menuItems): array
    {
        return array_filter($menuItems, static function(UserMenuItem $userMenuItem) use ($dic, $repository, $userSession)
        {
            if ($userMenuItem->checkVisibility !== null)
            {
                $visible = $dic->callClosureWithDependencyInjection($userMenuItem->checkVisibility);
                if (!$visible)
                {
                    return false;
                }
            }

            $level = $userMenuItem->level;
            if ($userSession->getLevel() >= $level)
            {
                return true;
            }
            $currentUser = $userSession->getProfile();
            $right = $userMenuItem->right ?? '';
            if ($right !== '' && $currentUser !== null && $repository->userHasRight($currentUser, $right))
            {
                return true;
            }

            return false;
        });
    }
}
