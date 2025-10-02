<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout\Ticket;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class OrderTicketType extends Model
{
    public const TABLE = 'geelhoed_tryout_order_tickettype';

    #[DatabaseField(dbName: 'orderId')]
    public Order $order;

    #[DatabaseField(dbName: 'typeId')]
    public Type $type;

    #[DatabaseField]
    public int $amount;
}
