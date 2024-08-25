<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\DBAL\Model;

final class Subscriber extends Model
{
    public const TABLE = 'geelhoed_clubactie_subscriber';
    public const TABLE_FIELDS = ['firstName', 'tussenvoegsel', 'lastName', 'email'];

    public string $firstName = '';
    public string $tussenvoegsel = '';
    public string $lastName = '';
    public string $email = '';
}
