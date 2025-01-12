<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\TicketType;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\Concert\Concert;

final class TicketType extends Model
{
    public const TABLE = 'ticketsale_tickettypes';

    #[DatabaseField]
    public int $concertId;
    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public float $price = 0.00;

    /**
     * @param Concert $concert
     * @return self[]
     */
    public static function loadByConcert(Concert $concert): array
    {
        return self::fetchAll(['concertId = ?'], [$concert->id], 'ORDER BY id');
    }
}
