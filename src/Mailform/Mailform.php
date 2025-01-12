<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Mailform extends Model
{
    public const TABLE = 'mailforms';

    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public string $email;
    #[DatabaseField]
    public string $antiSpamAnswer;
    #[DatabaseField]
    public bool $sendConfirmation;
    #[DatabaseField]
    public string|null $confirmationText;
}
