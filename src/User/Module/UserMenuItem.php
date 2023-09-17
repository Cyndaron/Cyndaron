<?php
declare(strict_types=1);

namespace Cyndaron\User\Module;

final class UserMenuItem
{
    public function __construct(
        public readonly string $label,
        public readonly string $link,
        public readonly int $level,
        public readonly string|null $right = null,
        public readonly string|null $icon = null,
    ) {
    }
}
