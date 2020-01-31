<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\UserLevel;

class ConcertController extends Controller
{
    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getConcertInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
        'viewReservedSeats' => ['level' => UserLevel::ADMIN, 'function' => 'viewReservedSeats'],
    ];

    protected function getConcertInfo()
    {
        $concertId = (int)Request::getVar(2);
        $concert = new Concert($concertId);
        $concert->load();
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_tickettypes WHERE concertId=? ORDER BY price DESC', [$concertId]);

        $answer = [
            'tickettypes' => [],
            'forcedDelivery' => (bool)$concert->forcedDelivery,
            'defaultDeliveryCost' => $concert->deliveryCost,
            'reservedSeatCharge' => $concert->reservedSeatCharge,
        ];

        foreach ($ticketTypes as $kaartsoort)
        {
            $answer['tickettypes'][] = [
                'id' => $kaartsoort['id'],
                'price' => $kaartsoort['price']
            ];
        }

        echo json_encode($answer);
    }

    protected function order()
    {
        $id = (int)Request::getVar(2);
        new OrderTicketsPage($id);
    }

    protected function viewOrders()
    {
        $id = (int)Request::getVar(2);
        new ConcertOrderOverviewPage($id);
    }

    protected function viewReservedSeats()
    {
        $id = (int)Request::getVar(2);
        new ShowReservedSeats($id);
    }
}