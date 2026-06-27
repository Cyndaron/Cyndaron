<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Closure;

final class PageManagerTab
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        /** @var Closure|class-string<PageManagerTabInterface> */
        public readonly Closure|string $tabDraw,
        public readonly string|null $js,
    ) {
    }
}
