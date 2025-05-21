<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads\Classification;

enum Type
{
    case PACKAGE;
    case PORTABLE;
    case INSTALLER;
    case SYMBOLS;
    case DEB_PACKAGE;
    case RPM_PACKAGE;

    public function getFriendlyName(): string
    {
        return match ($this)
        {
            self::PACKAGE => 'Package',
            self::PORTABLE => 'Portable',
            self::INSTALLER => 'Installer',
            self::SYMBOLS => 'Symbols',
            self::DEB_PACKAGE => 'DEB package',
            self::RPM_PACKAGE => 'RPM package',
        };
    }
}
