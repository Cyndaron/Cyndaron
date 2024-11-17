<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Page\Page;

final class ShopPage extends Page
{
    private const GYMTAS_ID = 1;

    public function __construct(Subscriber $subscriber, Order $order)
    {
        parent::__construct('Webwinkel');

        $this->addCss('/src/Geelhoed/Webshop/css/webshop.css');
        $this->addScript('/src/Geelhoed/Webshop/js/ShopPage.js');

        $products = Product::fetchAll();

        $orderItems = OrderItem::fetchAll(['orderId = ?'], [$order->id]);
        $hasGymtasInCart = false;

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

            if ($orderItem->productId === self::GYMTAS_ID && $orderItem->currency === Currency::LOTTERY_TICKET)
            {
                $hasGymtasInCart = true;
            }
        }
        $this->addTemplateVars([
            'hash' => $subscriber->hash,
            'numSoldTickets' => $subscriber->numSoldTickets,
            'numSpentTickets' => 0,
            'products' => $products,
            'gymtas' => $products[self::GYMTAS_ID],
            'order' => $order,
            'orderItems' => $orderItems,
            'hasGymtasInCart' => $hasGymtasInCart,
            'ticketSubtotal' => $ticketSubtotal,
            'euroSubtotal' => $euroSubtotal,
        ]);
    }
}
