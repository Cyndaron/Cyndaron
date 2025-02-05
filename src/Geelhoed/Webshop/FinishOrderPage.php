<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Page\Page;

final class FinishOrderPage extends Page
{
    public function __construct(
        Subscriber $subscriber,
        Order $order,
        LocationRepository $locationRepository,
        OrderRepository $orderRepository,
    ) {
        $this->title = 'Bestelling bevestigen';
        $this->addScript('/src/Geelhoed/Webshop/js/FinishOrderPage.js');

        $orderItems = OrderItem::fetchAll(['orderId = ?'], [$order->id]);

        $locations = ['' => '(selecteer een locatie)'];
        foreach ($locationRepository->getWithLessions() as $location)
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
            'ticketSubtotal' => $orderRepository->getTicketTotal($order),
            'euroSubtotal' => $orderRepository->getEuroSubtotal($order),
            'locations' => $locations,
        ]);
    }
}
