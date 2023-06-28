<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

final class PageManagerTab
{
    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly string $tabDraw,
        public readonly string|null $js,
    ) {
    }
}
