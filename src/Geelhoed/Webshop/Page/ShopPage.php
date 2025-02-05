<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Webshop\Model\Currency;
use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItemRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Geelhoed\Webshop\Model\ProductRepository;
use Cyndaron\Page\Page;
use function array_filter;
use function usort;

final class ShopPage extends Page
{
    public function __construct(
        Subscriber $subscriber,
        Order $order,
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        ProductRepository $productRepository
    ) {
        $this->title = 'Webwinkel';

        $this->addCss('/src/Geelhoed/Webshop/Page/css/webshop.css');
        $this->addScript('/src/Geelhoed/Webshop/Page/js/ShopPage.js');

        $products = array_filter(
            $productRepository->fetchAll(),
            static function(Product $product): bool
            {
                return $product->visible && $product->euroPrice !== null;
            }
        );
        usort($products, static function(Product $product1, Product $product2)
        {
            return (int)$product1->gcaTicketPrice <=> (int)$product2->gcaTicketPrice;
        });

        $gymtasProduct = $productRepository->fetchById(Product::GYMTAS_ID);

        $orderItems = $orderItemRepository->fetchAll(['orderId = ?'], [$order->id]);
        $hasGymtasInCart = false;

        foreach ($orderItems as $orderItem)
        {
            if ($orderItem->product->id === Product::GYMTAS_ID && $orderItem->currency === Currency::LOTTERY_TICKET)
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
            'ticketSubtotal' => $orderRepository->getTicketTotal($order),
            'euroSubtotal' => $orderRepository->getEuroSubtotal($order),
        ]);
    }
}
