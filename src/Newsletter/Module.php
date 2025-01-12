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
use Cyndaron\Util\Link;

final class Module implements Routes, UserMenuProvider
{
    public function routes(): array
    {
        return [
            'newsletter' =>  Controller::class,
        ];
    }

    public function getUserMenuItems(): array
    {
        return [
            new UserMenuItem(new Link('/newsletter/compose', 'Nieuwsbrief versturen'), UserLevel::ADMIN),
            new UserMenuItem(new Link('/newsletter/viewSubscribers', 'Abonnees nieuwsbrief'), UserLevel::ADMIN),
        ];
    }
}
