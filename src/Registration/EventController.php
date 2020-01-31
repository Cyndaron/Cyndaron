<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class EventController extends Controller
{
    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getEventInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
    ];

    protected function getEventInfo()
    {
        $eventId = (int)Request::getVar(2);
        $event = new Event($eventId);
        $event->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM registration_tickettypes WHERE eventId=? ORDER BY price DESC', [$eventId]);

        $answer = [
            'registrationCost0' => $event->registrationCost0,
            'registrationCost1' => $event->registrationCost1,
            'lunchCost' => $event->lunchCost,
            'tickettypes' => $ticketTypes,
        ];

        echo json_encode($answer);
    }

    protected function order()
    {
        $id = (int)Request::getVar(2);
        $event = Event::loadFromDatabase($id);
        new OrderTicketsPage($event);
    }

    protected function viewOrders()
    {
        $id = (int)Request::getVar(2);
        $event = Event::loadFromDatabase($id);
        new EventOrderOverviewPage($event);
    }
}