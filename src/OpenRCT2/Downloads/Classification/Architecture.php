<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads\Classification;

enum Architecture
{
    case X86_64;
    case X86_32;
    case ARM_64;
    case UNIVERSAL;
    case OTHER;

    public function getFriendlyName(): string
    {
        return match ($this)
        {
            self::X86_64 => 'x86 (64-bit)',
            self::X86_32 => 'x86 (32-bit)',
            self::ARM_64 => 'ARM (64-bit)',
            self::UNIVERSAL => 'Universal (hybrid x86/ARM)',
            self::OTHER => 'Unknown',
        };
    }
}
