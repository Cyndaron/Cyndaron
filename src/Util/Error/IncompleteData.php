<?php
namespace Cyndaron\Util\Error;

use Cyndaron\Util\UserSafeError;
use RuntimeException;

final class IncompleteData extends RuntimeException implements UserSafeError
{
}
