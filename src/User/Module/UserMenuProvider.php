<?php
declare(strict_types=1);

namespace Cyndaron\User\Module;

use Cyndaron\User\User;

interface UserMenuProvider
{
    /**
     * @return UserMenuItem[]
     */
    public function getUserMenuItems(): array;
}
