<?php
declare(strict_types=1);

namespace Cyndaron\Util;

enum FileCacheLoadResult
{
    case OK;
    case NO_CACHE_FILE_EXISTS;
    case CACHE_FILE_BROKEN;
    case CACHE_FILE_INACCESSIBLE;
}
