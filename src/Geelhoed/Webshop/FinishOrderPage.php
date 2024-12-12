<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Location\Location;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Page\Page;

final class FinishOrderPage extends Page
{
    public function __construct(Subscriber $subscriber, Order $order)
    {
        parent::__construct('Bestelling bevestigen');
        $this->addScript('/src/Geelhoed/Webshop/js/FinishOrderPage.js');

        $orderItems = OrderItem::fetchAll(['orderId = ?'], [$order->id]);

        $locations = ['' => '(selecteer een locatie)'];
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
            'ticketSubtotal' => $order->getTicketTotal(),
            'euroSubtotal' => $order->getEuroSubtotal(),
            'locations' => $locations,
        ]);
    }
}
