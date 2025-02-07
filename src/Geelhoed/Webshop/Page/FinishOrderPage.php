<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\OrderItemRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Page\Page;

final class FinishOrderPage extends Page
{
    public function __construct(
        Subscriber $subscriber,
        Order $order,
        LocationRepository $locationRepository,
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
    ) {
        $this->title = 'Bestelling bevestigen';
        $this->addScript('/src/Geelhoed/Webshop/Page/js/FinishOrderPage.js');

        $orderItems = $orderItemRepository->fetchAll(['orderId = ?'], [$order->id]);

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
