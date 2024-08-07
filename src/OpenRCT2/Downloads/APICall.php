<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use function sha1;

enum APICall : string
{
    case DEVELOP_BUILDS = 'https://api.github.com/repos/Limetric/OpenRCT2-binaries/releases';
    case LATEST_DEVELOP_BUILD = 'https://api.github.com/repos/Limetric/OpenRCT2-binaries/releases/latest';
    case RELEASE_BUILDS = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases';
    case LATEST_RELEASE_BUILD = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases/latest';
    case CHANGELOG = 'https://raw.githubusercontent.com/OpenRCT2/OpenRCT2/develop/distribution/changelog.txt';

    public function getUrl(): string
    {
        return $this->value;
    }

    public function getCachePath(): string
    {
        return CACHE_DIR . sha1($this->value);
    }

    public function getAPITimeout(): int
    {
        return match ($this)
        {
            self::LATEST_DEVELOP_BUILD, self::RELEASE_BUILDS => 30 * 60,
            default => 5 * 60,
        };
    }
}
