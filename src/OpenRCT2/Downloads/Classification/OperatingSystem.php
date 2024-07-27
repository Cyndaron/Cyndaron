<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads\Classification;

enum OperatingSystem
{
    case WINDOWS;
    case MACOS;
    case LINUX;
    case ANDROID;
    case OTHER;

    public function getFriendlyName(): string
    {
        return match ($this)
        {
            self::WINDOWS => 'Windows',
            self::MACOS => 'macOS',
            self::LINUX => 'Linux',
            self::ANDROID => 'Android',
            self::OTHER => 'Other',
        };
    }

    public function getPriority(): int
    {
        return match ($this)
        {
            self::WINDOWS => 1,
            self::MACOS => 2,
            self::LINUX => 3,
            self::ANDROID => 4,
            self::OTHER => 5,
        };
    }
}
