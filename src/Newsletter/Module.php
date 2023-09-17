<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Cyndaron\Module\Routes;
use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\User\Module\UserMenuProvider;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

final class Module implements Routes, UserMenuProvider
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
            new UserMenuItem('Nieuwsbrief versturen', '/newsletter/compose', UserLevel::ADMIN),
            new UserMenuItem('Abonnees nieuwsbrief', '/newsletter/viewSubscribers', UserLevel::ADMIN),
        ];
    }
}
