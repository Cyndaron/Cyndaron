<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\OldLauncherAPI;

enum Flavour : int
{
    case WINDOWS_X86_32 = 1;
    case MACOS = 3;
    case WINDOWS_X86_64 = 6;
    case LINUX_X86_32 = 4;
    case LINUX_X86_64 = 9;
}
