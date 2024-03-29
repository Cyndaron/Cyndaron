<?php
namespace Cyndaron\Registration;

use Cyndaron\DBAL\Model;

final class RegistrationTicketType extends Model
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
        return self::fetchAll(['orderId = ?'], [$registration->id]);
    }
}
