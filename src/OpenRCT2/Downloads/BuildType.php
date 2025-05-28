<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

enum BuildType : string
{
    case RELEASE = 'release';
    case DEVELOP = 'develop';
    case LAUNCHER = 'launcher';

    public function hasChangelog(): bool
    {
        return $this === BuildType::RELEASE || $this === BuildType::LAUNCHER;
    }
}
