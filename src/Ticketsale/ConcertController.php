<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Routing\Controller;
use Cyndaron\Page;
use Cyndaron\User\UserLevel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function assert;

final class ConcertController extends Controller
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
        if ($concertId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $concert = new Concert($concertId);
        $concert->load();
        /** @var TicketType[] $ticketTypes */
        $ticketTypes = TicketType::fetchAll(['concertId = ?'], [$concertId], 'ORDER BY price DESC');

        $answer = [
            'tickettypes' => [],
            'forcedDelivery' => (bool)$concert->forcedDelivery,
            'defaultDeliveryCost' => $concert->deliveryCost,
            'reservedSeatCharge' => $concert->reservedSeatCharge,
        ];

        foreach ($ticketTypes as $ticketType)
        {
            $answer['tickettypes'][] = [
                'id' => $ticketType->id,
                'price' => $ticketType->price,
            ];
        }

        return new JsonResponse($answer);
    }

    protected function order(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new Page('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $page = new OrderTicketsPage($id);
        return new Response($page->render());
    }

    protected function viewOrders(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new Page('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::loadFromDatabase($id);
        assert($concert !== null);
        $page = new ConcertOrderOverviewPage($concert);
        return new Response($page->render());
    }

    protected function viewReservedSeats(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new Page('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::loadFromDatabase($id);
        assert($concert !== null);
        $page = new ShowReservedSeats($concert);
        return new Response($page->render());
    }
}
