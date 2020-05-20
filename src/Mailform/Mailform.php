<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\Model;

class Mailform extends Model
{
    public const TABLE = 'mailforms';
    public const TABLE_FIELDS = ['name', 'email', 'antiSpamAnswer', 'sendConfirmation', 'confirmationText'];

    public string $name;
    public string $email;
    public string $antiSpamAnswer;
    public bool $sendConfirmation;
    public ?string $confirmationText;
}
