<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Attribute;
use Cyndaron\Request\RequestMethod;

#[Attribute(Attribute::TARGET_METHOD | Attribute::IS_REPEATABLE)]
final class RouteAttribute
{
    public function __construct(
        public readonly string $action,
        public readonly RequestMethod $method,
        public readonly int $level,
        public readonly bool $isApiMethod = false,
        public readonly string|null $right = null,
        public readonly bool $skipCSRFCheck = false,
    ) {
    }
}
