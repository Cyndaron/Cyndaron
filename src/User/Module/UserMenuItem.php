<?php
declare(strict_types=1);

namespace Cyndaron\User\Module;

use Cyndaron\Util\Link;

final class UserMenuItem
{
    public function __construct(
        public readonly Link $link,
        public readonly int $level,
        public readonly string|null $right = null,
    ) {
    }
}
