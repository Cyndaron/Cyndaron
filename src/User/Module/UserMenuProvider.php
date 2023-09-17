<?php
declare(strict_types=1);

namespace Cyndaron\User\Module;

use Cyndaron\User\User;

interface UserMenuProvider
{
    public function getUserMenuItems(?User $profile): array;
}
