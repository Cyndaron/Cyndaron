<?php
declare(strict_types=1);

namespace Cyndaron\Util;

/**
 * Can be ‘implemented’ by any Throwable that does not contain any sensitive date
 * and may safely be presented to the user.
 */
interface UserSafeError
{
}
