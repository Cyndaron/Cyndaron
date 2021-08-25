<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\User\User;

interface UserMenu
{
    public function getUserMenuItems(?User $profile): array;
}
