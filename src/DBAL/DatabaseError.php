<?php
namespace Cyndaron\DBAL;

use Cyndaron\Util\UserSafeError;
use RuntimeException;

final class DatabaseError extends RuntimeException implements UserSafeError
{
}
