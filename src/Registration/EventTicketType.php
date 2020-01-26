<?php
namespace Cyndaron\Registration;

use Cyndaron\Model;

class EventTicketType extends Model
{
    const TABLE = 'registration_tickettypes';
    const TABLE_FIELDS = ['eventId', 'name', 'price', 'discountPer5'];

    public int $eventId;
    public string $name;
    public string $price;
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