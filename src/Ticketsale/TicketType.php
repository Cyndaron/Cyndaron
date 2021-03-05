<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\Model;

final class TicketType extends Model
{
    public const TABLE = 'ticketsale_tickettypes';
    public const TABLE_FIELDS = ['concertId', 'name', 'price'];

    public int $concertId;
    public string $name = '';
    public float $price = 0.00;
}
