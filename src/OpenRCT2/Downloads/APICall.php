<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use function sha1;
use function file_exists;

enum APICall : string
{
    case DEVELOP_BUILDS = 'https://api.github.com/repos/OpenRCT2/OpenRCT2-binaries/releases?per_page=120';
    case LATEST_DEVELOP_BUILD = 'https://api.github.com/repos/OpenRCT2/OpenRCT2-binaries/releases/latest';
    case RELEASE_BUILDS = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases';
    case LATEST_RELEASE_BUILD = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases/latest';
    case LAUNCHER_BUILDS = 'https://api.github.com/repos/OpenRCT2/OpenLauncher/releases';
    case LATEST_LAUNCHER_BUILD = 'https://api.github.com/repos/OpenRCT2/OpenLauncher/releases/latest';
    case CHANGELOG = 'https://raw.githubusercontent.com/OpenRCT2/OpenRCT2/develop/distribution/changelog.txt';

    public function getUrl(): string
    {
        return $this->value;
    }

    public function getCachePath(): string
    {
        return CACHE_DIR . 'openrct2/' . sha1($this->value);
    }

    public function getAPITimeout(): int
    {
        return match ($this)
        {
            self::LATEST_RELEASE_BUILD, self::RELEASE_BUILDS => 30 * 60,
            self::LATEST_LAUNCHER_BUILD, self::LAUNCHER_BUILDS => 4 * 7 * 24 * 60 * 60,
            default => 5 * 60,
        };
    }

    public function getBuildType(): BuildType
    {
        return match ($this)
        {
            self::LATEST_RELEASE_BUILD, self::RELEASE_BUILDS => BuildType::RELEASE,
            self::LATEST_DEVELOP_BUILD, self::DEVELOP_BUILDS => BuildType::DEVELOP,
            self::LATEST_LAUNCHER_BUILD, self::LAUNCHER_BUILDS => BuildType::LAUNCHER,
            default => throw new \Exception('Not a build type!'),
        };
    }

    public function presentInCache(): bool
    {
        return file_exists($this->getCachePath());
    }
}
