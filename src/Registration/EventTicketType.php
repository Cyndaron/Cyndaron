<?php
namespace Cyndaron\Registration;

use Cyndaron\Model;

class EventTicketType extends Model
{
    const TABLE = 'registration_tickettypes';
    const TABLE_FIELDS = ['eventId', 'name', 'price'];

    public $eventId;
    public $name;
    public $price;

    /**
     * @param Event $event
     * @return EventTicketType[]
     */
    public static function loadByEvent(Event $event): array
    {
        return static::fetchAll(['eventId = ?'], [$event->id], 'ORDER BY price DESC');
    }
}