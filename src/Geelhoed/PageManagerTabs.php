<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Clubactie\Subscriber;
use Cyndaron\Geelhoed\Clubactie\SubscriberRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Location\LocationRepository;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\Geelhoed\Webshop\Model\OrderRepository;
use Cyndaron\Geelhoed\Webshop\Model\ProductRepository;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;
use function usort;

final class PageManagerTabs
{
    public static function membersTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, LocationRepository $locationRepository, SportRepository $sportRepository): string
    {
        return $templateRenderer->render('Geelhoed/Member/PageManagerTab', [
            'locations' => $locationRepository->fetchAll(afterWhere: 'ORDER BY city, street'),
            'tokenDelete' => $tokenHandler->get('member', 'delete'),
            'tokenSave' => $tokenHandler->get('member', 'save'),
            'tokenRemoveGraduation' => $tokenHandler->get('member', 'removeGraduation'),
            'sports' => $sportRepository->fetchAll(),
            'locationRepository' => $locationRepository,
        ]);
    }

    public static function contestsTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, ContestRepository $contestRepository, SportRepository $sportRepository): string
    {
        $contests = $contestRepository->fetchAll([], [], 'ORDER BY registrationDeadline DESC');
        return $templateRenderer->render('Geelhoed/Contest/PageManagerTab', [
            'contests' => $contests,
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
        return $templateRenderer->render('Geelhoed/Webshop/PageManagerTabOrder', [
            'orders' => $orders,
            'orderRepository' => $orderRepository,
        ]);
    }

    public static function productsTab(TemplateRenderer $templateRenderer, ProductRepository $repository): string
    {
        $products = $repository->fetchAll();
        return $templateRenderer->render('Geelhoed/Webshop/PageManagerTabProduct', [
            'products' => $products,
        ]);
    }
}
