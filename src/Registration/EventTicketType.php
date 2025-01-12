<?php
namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class EventTicketType extends Model
{
    public const TABLE = 'registration_tickettypes';

    #[DatabaseField]
    public int $eventId;
    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public float $price;
    #[DatabaseField]
    public bool $discountPer5 = false;

    /**
     * @param Event $event
     * @return EventTicketType[]
     */
    public static function loadByEvent(Event $event): array
    {
        return self::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY price DESC');
    }
}
