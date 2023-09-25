<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class VisibleFaces
{
    public function __construct(
        /** @var string[] */
        public readonly array $front,
        /** @var string[] */
        public readonly array $back,
    ) {
    }
}
