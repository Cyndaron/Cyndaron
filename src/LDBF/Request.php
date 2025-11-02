<?php
declare(strict_types=1);

namespace Cyndaron\LDBF;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Request extends Model
{
    public const TABLE = 'ldbf_mailform_request';

    #[DatabaseField]
    public string $secretCode = '';
    #[DatabaseField]
    public string $email = '';
    #[DatabaseField]
    public string $mailBody = '';
    #[DatabaseField]
    public bool $confirmed = false;
}
