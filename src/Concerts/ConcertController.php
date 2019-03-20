<?php
declare (strict_types = 1);

namespace Cyndaron\Concerts;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class ConcertController extends Controller
{
    protected $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getConcertInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
        'viewReservedSeats' => ['level' => UserLevel::ADMIN, 'function' => 'viewReservedSeats'],
    ];

    protected function getConcertInfo()
    {
        $concertId = intval(Request::getVar(2));
        $concertInfo = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM kaartverkoop_concerten WHERE id=?', [$concertId]);
        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM kaartverkoop_kaartsoorten WHERE concert_id=? ORDER BY prijs DESC', [$concertId]);

        $answer = [
            'kaartsoorten' => [],
            'bezorgenVerplicht' => boolval($concertInfo['bezorgen_verplicht']),
            'standaardVerzendkosten' => $concertInfo['verzendkosten'],
            'toeslagGereserveerdePlaats' => $concertInfo['toeslag_gereserveerde_plaats']
        ];

        foreach ($ticketTypes as $kaartsoort)
        {
            $answer['kaartsoorten'][] = [
                'id' => $kaartsoort['id'],
                'prijs' => $kaartsoort['prijs']
            ];
        }

        echo json_encode($answer);
    }

    protected function order()
    {
        $id = intval(Request::getVar(2));
        new OrderTicketsPage($id);
    }

    protected function viewOrders()
    {
        $id = intval(Request::getVar(2));
        new ConcertOrderOverviewPage($id);
    }

    protected function viewReservedSeats()
    {
        $id = intval(Request::getVar(2));
        new ShowReservedSeats($id);
    }
}