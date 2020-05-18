<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ConcertController extends Controller
{
    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getConcertInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
        'viewReservedSeats' => ['level' => UserLevel::ADMIN, 'function' => 'viewReservedSeats'],
    ];

    protected function getConcertInfo(): JsonResponse
    {
        $concertId = $this->queryBits->getInt(2);
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

        return new JsonResponse($answer);
    }

    protected function order(): Response
    {
        $id = $this->queryBits->getInt(2);
        $page = new OrderTicketsPage($id);
        return new Response($page->render());
    }

    protected function viewOrders(): Response
    {
        $id = $this->queryBits->getInt(2);
        $concert = Concert::loadFromDatabase($id);
        $page = new ConcertOrderOverviewPage($concert);
        return new Response($page->render());
    }

    protected function viewReservedSeats(): Response
    {
        $id = $this->queryBits->getInt(2);
        $concert = Concert::loadFromDatabase($id);
        $page = new ShowReservedSeats($concert);
        return new Response($page->render());
    }
}