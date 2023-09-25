<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class PolygonsInFace
{
    public function __construct(
        /** @var Polygon[] */
        public array $top = [],
        /** @var Polygon[] */
        public array $bottom = [],
        /** @var Polygon[] */
        public array $left = [],
        /** @var Polygon[] */
        public array $right = [],
        /** @var Polygon[] */
        public array $front = [],
        /** @var Polygon[] */
        public array $back = [],
    ) {
    }
}
