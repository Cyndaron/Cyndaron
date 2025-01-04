<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Page\Page;
use function array_filter;
use function usort;

final class ShopPage extends Page
{
    public function __construct(Subscriber $subscriber, Order $order)
    {
        parent::__construct('Webwinkel');

        $this->addCss('/src/Geelhoed/Webshop/css/webshop.css');
        $this->addScript('/src/Geelhoed/Webshop/js/ShopPage.js');

        $products = array_filter(
            Product::fetchAll(),
            static function(Product $product): bool
            {
                return $product->visible && $product->euroPrice !== null;
            }
        );
        usort($products, static function(Product $product1, Product $product2)
        {
            return (int)$product1->gcaTicketPrice <=> (int)$product2->gcaTicketPrice;
        });

        $gymtasProduct = Product::fetchById(Product::GYMTAS_ID);

        $orderItems = OrderItem::fetchAll(['orderId = ?'], [$order->id]);
        $hasGymtasInCart = false;

        foreach ($orderItems as $orderItem)
        {
            if ($orderItem->productId === Product::GYMTAS_ID && $orderItem->currency === Currency::LOTTERY_TICKET)
            {
                $hasGymtasInCart = true;
                break;
            }
        }
        $this->addTemplateVars([
            'hash' => $subscriber->hash,
            'numSoldTickets' => $subscriber->numSoldTickets,
            'numSpentTickets' => 0,
            'products' => $products,
            'gymtas' => $gymtasProduct,
            'order' => $order,
            'orderItems' => $orderItems,
            'hasGymtasInCart' => $hasGymtasInCart,
            'needsAddingGymtas' => $subscriber->numSoldTickets >= 10 && !$hasGymtasInCart,
            'ticketSubtotal' => $order->getTicketTotal(),
            'euroSubtotal' => $order->getEuroSubtotal(),
        ]);
    }
}
