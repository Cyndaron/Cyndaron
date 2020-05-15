<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class EventController extends Controller
{
    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getEventInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
    ];

    protected function getEventInfo(): JsonResponse
    {
        $eventId = $this->queryBits->getInt(2);
        $event = new Event($eventId);
        $event->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM registration_tickettypes WHERE eventId=? ORDER BY price DESC', [$eventId]);

        $answer = [
            'registrationCost0' => $event->registrationCost0,
            'registrationCost1' => $event->registrationCost1,
            'registrationCost2' => $event->registrationCost2,
            'lunchCost' => $event->lunchCost,
            'tickettypes' => $ticketTypes,
        ];

        return new JsonResponse($answer);
    }

    protected function order(): Response
    {
        $id = $this->queryBits->getInt(2);
        $event = Event::loadFromDatabase($id);
        $page = new OrderPage($event);
        return new Response($page->render());
    }

    protected function viewOrders(): Response
    {
        $id = $this->queryBits->getInt(2);
        $event = Event::loadFromDatabase($id);
        $page = new EventOrderOverviewPage($event);
        return new Response($page->render());
    }
}