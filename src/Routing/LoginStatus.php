<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

enum LoginStatus
{
    case OK;
    case NEEDS_LOGIN;
    case INSUFFICIENT_RIGHTS;
}
