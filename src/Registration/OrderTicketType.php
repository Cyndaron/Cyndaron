<?php
namespace Cyndaron\Registration;

use Cyndaron\Model;

class OrderTicketType extends Model
{
    public const TABLE = 'registration_orders_tickettypes';
    public const TABLE_FIELDS = ['orderId', 'tickettypeId', 'amount'];

    public int $orderId;
    public int $tickettypeId;
    public int $amount;

    /**
     * @param Order $order
     * @return OrderTicketType[]
     */
    public static function loadByOrder(Order $order): array
    {
        return static::fetchAll(['orderId = ?'], [$order->id]);
    }
}