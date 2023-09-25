<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft\Skin;

final class FacesByBodyPart
{
    public function __construct(
        public readonly PolygonsInFace $helmet,
        public readonly PolygonsInFace $head,
        public readonly PolygonsInFace $torso,
        public readonly PolygonsInFace $rightArm,
        public readonly PolygonsInFace $leftArm,
        public readonly PolygonsInFace $rightLeg,
        public readonly PolygonsInFace $leftLeg,
    ) {
    }
}
