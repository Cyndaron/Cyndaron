<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\Util\DependencyInjectionContainer;
use function array_filter;

final class UserMenu
{
    public function __construct(
        private readonly DependencyInjectionContainer $dic,
        private readonly UserRepository $userRepository,
    ) {
    }

    /**
     * @param UserSession $userSession
     * @param UserMenuItem[] $menuItems
     * @return UserMenuItem[]
     */
    public function getForCurrentSession(UserSession $userSession, array $menuItems): array
    {
        return array_filter($menuItems, function(UserMenuItem $userMenuItem) use ($userSession)
        {
            if ($userMenuItem->checkVisibility !== null)
            {
                $visible = $this->dic->callClosureWithDependencyInjection($userMenuItem->checkVisibility);
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
            if ($right !== '' && $currentUser !== null && $this->userRepository->userHasRight($currentUser, $right))
            {
                return true;
            }

            return false;
        });
    }
}
