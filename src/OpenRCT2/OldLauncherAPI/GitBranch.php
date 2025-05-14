<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\OldLauncherAPI;

enum GitBranch : string
{
    case RELEASE = 'master';
    case DEVELOP = 'develop';
}
