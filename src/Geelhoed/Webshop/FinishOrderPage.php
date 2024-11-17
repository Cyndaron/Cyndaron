<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Page\Page;

final class FinishOrderPage extends Page
{
    public function __construct(Subscriber $subscriber, Order $order)
    {
        parent::__construct('Bestelling bevestigen');

        $orderItems = OrderItem::fetchAll(['orderId = ?'], [$order->id]);

        $ticketSubtotal = 0;
        $euroSubtotal = 0.00;

        foreach ($orderItems as $orderItem)
        {
            if ($orderItem->currency === Currency::LOTTERY_TICKET)
            {
                $ticketSubtotal += $orderItem->price;
            }
            else
            {
                $euroSubtotal += $orderItem->price;
            }
        }

        $days = [
            1 => 'Maandag',
            2 => 'Dinsdag',
            3 => 'Woensdag',
            4 => 'Donderdag',
            5 => 'Vrijdag',
            6 => 'Zaterdag'
        ];
        $locations = [];
        foreach (Location::getWithLessions() as $location)
        {
            $locations[$location->id] = $location->getName();
        }

        $this->addTemplateVars([
            'subscriber' => $subscriber,
            'hash' => $subscriber->hash,
            'numSoldTickets' => $subscriber->numSoldTickets,
            'numSpentTickets' => 0,
            'order' => $order,
            'orderItems' => $orderItems,
            'ticketSubtotal' => $ticketSubtotal,
            'euroSubtotal' => $euroSubtotal,
            'locations' => $locations,
            'days' => $days,
        ]);
    }
}
