<?php
/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\User\UserLevel;

final class Route
{
    public function __construct(
        public readonly string $function,
        public readonly int $level = UserLevel::ADMIN,
        public readonly string|null $right = null,
        public readonly bool $skipCSRFCheck = false,
    ) {
    }
}
