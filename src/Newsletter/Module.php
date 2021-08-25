<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Module\Routes;
use Cyndaron\Module\UserMenu;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

final class Module implements Routes, UserMenu
{
    public function routes(): array
    {
        return [
            'newsletter' =>  Controller::class,
        ];
    }

    public function getUserMenuItems(?User $profile): array
    {
        return [
            ['label' => 'Nieuwsbrief versturen', 'link' => '/newsletter/compose', 'level' => UserLevel::ADMIN],
            ['label' => 'Abonnees nieuwsbrief', 'link' => '/newsletter/viewSubscribers', 'level' => UserLevel::ADMIN],
        ];
    }
}
