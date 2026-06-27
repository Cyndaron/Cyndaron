<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Clubactie\SubscriberRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Geelhoed\Tryout\Ticket\OrderTicketTypeRepository;
use Cyndaron\Geelhoed\Tryout\Ticket\OrderTotal;
use Cyndaron\Geelhoed\Tryout\Ticket\TypeRepository;
use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Geelhoed\Webshop\Model\ProductRepository;
use Cyndaron\Request\QueryBits;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\TemplateRenderer;
use DateInterval;
use DateTime;
use function usort;
use function array_key_exists;

final class PageManagerTabs
{
    public static function ordersTab(TemplateRenderer $templateRenderer, OrderRepository $orderRepository): string
    {
        $orders = $orderRepository->fetchAll();
        return $templateRenderer->render('Geelhoed/Webshop/Page/PageManagerTabOrder', [
            'orders' => $orders,
            'orderRepository' => $orderRepository,
        ]);
    }

    public static function productsTab(TemplateRenderer $templateRenderer, ProductRepository $repository): string
    {
        $products = $repository->fetchAll();
        return $templateRenderer->render('Geelhoed/Webshop/Page/PageManagerTabProduct', [
            'products' => $products,
        ]);
    }

    public static function tryoutTicketTypesTab(TemplateRenderer $templateRenderer, TypeRepository $repository): string
    {
        $types = $repository->fetchAll();
        return $templateRenderer->render('Geelhoed/Tryout/Ticket/PageManagerTab', [
            'types' => $types,
        ]);
    }
}
