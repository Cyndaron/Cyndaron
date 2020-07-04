<?php
namespace Cyndaron\Registration;

use Cyndaron\Model;

final class EventTicketType extends Model
{
    public const TABLE = 'registration_tickettypes';
    public const TABLE_FIELDS = ['eventId', 'name', 'price', 'discountPer5'];

    public int $eventId;
    public string $name;
    public float $price;
    public bool $discountPer5 = false;

    /**
     * @param Event $event
     * @return EventTicketType[]
     */
    public static function loadByEvent(Event $event): array
    {
        return static::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY price DESC');
    }
}
