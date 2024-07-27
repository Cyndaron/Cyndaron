<?php
declare(strict_types=1);

namespace Cyndaron\Util;

enum BuiltinSetting : string
{
    case ORGANISATION = 'organisation';
    case SHORT_CODE = 'shortCode';
    case LANGUAGE = 'language';
}
