<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class PartAngles
{
    public function __construct(
        public readonly float $cosAlpha,
        public readonly float $sinAlpha,
        public readonly float $cosOmega,
        public readonly float $sinOmega,
    )
    {
    }
}
