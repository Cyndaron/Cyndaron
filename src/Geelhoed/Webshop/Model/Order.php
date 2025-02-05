<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Hour\Hour;

final class Order extends Model
{
    public const TABLE = 'geelhoed_webshop_order';

    #[DatabaseField(dbName: 'subscriberId')]
    public Subscriber $subscriber;
    #[DatabaseField(dbName: 'hourId')]
    public Hour $hour;
    #[DatabaseField]
    public OrderStatus $status = OrderStatus::QUOTE;
    #[DatabaseField]
    public string $paymentId = '';
}
