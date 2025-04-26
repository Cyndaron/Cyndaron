<?php
namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class RegistrationTicketType extends Model
{
    public const TABLE = 'registration_orders_tickettypes';

    #[DatabaseField(dbName: 'orderId')]
    public Registration $registration;
    #[DatabaseField(dbName: 'tickettypeId')]
    public EventTicketType $ticketType;
    #[DatabaseField]
    public int $amount;
}
