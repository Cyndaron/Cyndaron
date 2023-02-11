<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class CoordsXYZ
{
    public function __construct(public readonly float $x, public readonly float $y, public readonly float $z)
    {
    }
}
