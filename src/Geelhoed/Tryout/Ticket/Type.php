<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Type extends Model
{
    public const TABLE = 'geelhoed_tryout_tickettype';

    #[DatabaseField]
    public string $name = '';

    #[DatabaseField]
    public string $annotation = '';

    #[DatabaseField]
    public float $price = 0.0;
}
