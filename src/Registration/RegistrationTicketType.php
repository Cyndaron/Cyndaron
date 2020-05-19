<?php
namespace Cyndaron\Registration;

use Cyndaron\Model;

class RegistrationTicketType extends Model
{
    public const TABLE = 'registration_orders_tickettypes';
    public const TABLE_FIELDS = ['orderId', 'tickettypeId', 'amount'];

    public int $orderId;
    public int $tickettypeId;
    public int $amount;

    /**
     * @param Registration $registration
     * @return RegistrationTicketType[]
     */
    public static function loadByRegistration(Registration $registration): array
    {
        return static::fetchAll(['orderId = ?'], [$registration->id]);
    }
}