<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Tryout\Tryout;

final class Order extends Model
{
    public const TABLE = 'geelhoed_tryout_order';

    #[DatabaseField(dbName: 'tryoutId')]
    public Tryout $tryout;

    #[DatabaseField]
    public string $name = '';

    #[DatabaseField]
    public string $email = '';

    #[DatabaseField]
    public bool $isPaid = false;

    #[DatabaseField]
    public string $transactionCode = '';
}
