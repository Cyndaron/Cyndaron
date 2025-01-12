<?php
namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class RegistrationTicketType extends Model
{
    public const TABLE = 'registration_orders_tickettypes';

    #[DatabaseField]
    public int $orderId;
    #[DatabaseField]
    public int $tickettypeId;
    #[DatabaseField]
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
