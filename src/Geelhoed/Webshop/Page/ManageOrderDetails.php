<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Geelhoed\Webshop\Model\Order;
use Cyndaron\Geelhoed\Webshop\Model\OrderItemRepository;
use Cyndaron\Page\Page;

final class ManageOrderDetails
{
    public function __construct(
        private readonly OrderItemRepository $orderItemRepository,
    ) {
    }

    public function createPage(Order $order): Page
    {
        $fullName = $order->subscriber->getFullName();
        $page = new Page();
        $page->template = 'Geelhoed/Webshop/Page/ManageOrderDetailsPage';
        $page->title = "Orderdetails: {$order->id}, {$fullName}";
        $page->addTemplateVars([
            'orderItems' => $this->orderItemRepository->fetchAllByOrder($order),
        ]);

        return $page;
    }
}
