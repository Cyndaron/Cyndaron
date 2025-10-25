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
    public static function membersTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, LocationRepository $locationRepository, SportRepository $sportRepository, GraduationRepository $graduationRepository): string
    {
        return $templateRenderer->render('Geelhoed/Member/PageManagerTab', [
            'locations' => $locationRepository->fetchAll(afterWhere: 'ORDER BY city, street'),
            'tokenDelete' => $tokenHandler->get('member', 'delete'),
            'tokenSave' => $tokenHandler->get('member', 'save'),
            'tokenRemoveGraduation' => $tokenHandler->get('member', 'removeGraduation'),
            'sports' => $sportRepository->fetchAll(),
            'locationRepository' => $locationRepository,
            'graduations' => $graduationRepository->fetchAll(),
        ]);
    }

    public static function contestsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, ContestRepository $contestRepository, ContestDateRepository $contestDateRepository, SportRepository $sportRepository): string
    {
        $contests = $contestRepository->fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return $templateRenderer->render('Geelhoed/Contest/Page/PageManagerTab', [
            'contests' => $contests,
            'contestRepository' => $contestRepository,
            'contestDateRepository' => $contestDateRepository,
            'tokenEdit' => $tokenHandler->get('contest', 'edit'),
            'tokenDelete' => $tokenHandler->get('contest', 'delete'),
            'sports' => $sportRepository->fetchAllForSelect(),
        ]);
    }

    public static function sportsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, SportRepository $repository): string
    {
        $sports = $repository->fetchAll();
        return $templateRenderer->render('Geelhoed/Sport/PageManagerTab', [
            'sports' => $sports,
            'tokenEdit' => $tokenHandler->get('sport', 'edit'),
        ]);
    }

    public static function tryoutTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, TryoutRepository $repository): string
    {
        $csrfTokenCreatePhotoalbums = $tokenHandler->get('tryout', 'create-photoalbums');
        $tryouts = $repository->fetchAll();
        return $templateRenderer->render('Geelhoed/Tryout/PageManagerTab', [
            'tryouts' => $tryouts,
            'csrfTokenCreatePhotoalbums' => $csrfTokenCreatePhotoalbums,
        ]);
    }

    public static function clubactieTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, SubscriberRepository $repository): string
    {
        $subscribers = $repository->fetchAll();
        usort($subscribers, function(Subscriber $s1, Subscriber $s2)
        {
            $sort1 = $s1->soldTicketsAreVerified <=> $s2->soldTicketsAreVerified;
            return ($sort1 !== 0) ? $sort1 : ($s1->lastName <=> $s2->lastName);
        });
        return $templateRenderer->render('Geelhoed/Clubactie/PageManagerTab', [
            'subscribers' => $subscribers,
            'tokenHandler' => $tokenHandler,
        ]);
    }

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

    public static function tryoutOrdersTab(TemplateRenderer $templateRenderer, QueryBits $queryBits, TryoutRepository $tryoutRepository, Tryout\Ticket\OrderRepository $orderRepository, OrderTicketTypeRepository $ottRepository): string
    {
        $eventId = $queryBits->getInt(2);
        $event = $tryoutRepository->fetchById($eventId);
        if ($event === null)
        {
            $now = new DateTime();
            $cutoff = new DateTime();
            $cutoff->add(new DateInterval('P1W'));
            $event = $tryoutRepository->fetch(
                ['start <= ?', 'end >= ?'],
                [$cutoff->format(Util::SQL_DATE_TIME_FORMAT), $now->format(Util::SQL_DATE_TIME_FORMAT)],
                'ORDER BY start'
            );
            if ($event == null)
            {
                throw new \Exception('Evenement niet gevonden!');
            }
        }

        $orderRecords = [];
        foreach ($orderRepository->fetchByEvent($event) as $order)
        {
            if (!$order->isPaid)
            {
                continue;
            }

            $orderRecords[$order->id]['order'] = $order;
            $orderTicketTypes = $ottRepository->fetchAllByOrder($order);
            $orderRecords[$order->id]['orderTotal'] = OrderTotal::fromOrderTicketTypes($orderTicketTypes);
        }

        usort($orderRecords, function(array $input1, array $input2)
        {
            return $input1['order']->name <=> $input2['order']->name;
        });

        return $templateRenderer->render('Geelhoed/Tryout/Ticket/PageManagerTabOrders', [
            'event' => $event,
            'orderRecords' => $orderRecords,
        ]);
    }
}
