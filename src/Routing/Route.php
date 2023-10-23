<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\User\UserLevel;

final class Route
{
    public string $method = '';
    public int $level = UserLevel::ADMIN;
    public string|null $right = null;

    public function __construct(string $method, int $level = UserLevel::ADMIN, string|null $right = null)
    {
        $this->method = $method;
        $this->level = $level;
        $this->right = $right;
    }
}
