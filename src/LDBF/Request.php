<?php
declare(strict_types=1);

namespace Cyndaron\LDBF;

use Cyndaron\DBAL\Model;

final class Request extends Model
{
    public string $secretCode = '';
    public string $email = '';
    public string $mailBody = '';
    public bool $confirmed = false;
}
