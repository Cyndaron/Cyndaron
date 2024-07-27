<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads\Classification;

enum Type
{
    case PACKAGE;
    case PORTABLE;
    case INSTALLER;
    case SYMBOLS;

    public function getFriendlyName(): string
    {
        return match ($this)
        {
            self::PACKAGE => 'Package',
            self::PORTABLE => 'Portable',
            self::INSTALLER => 'Installer',
            self::SYMBOLS => 'Symbols',
        };
    }
}
