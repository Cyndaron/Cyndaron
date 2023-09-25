<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class PartsAngles
{
    public function __construct(
        public readonly PartAngles $torso,
        public readonly PartAngles $head,
        public readonly PartAngles $helmet,
        public readonly PartAngles $rightArm,
        public readonly PartAngles $leftArm,
        public readonly PartAngles $rightLeg,
        public readonly PartAngles $leftLeg,
    ) {
    }
}
