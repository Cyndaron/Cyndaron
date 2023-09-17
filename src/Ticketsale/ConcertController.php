<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\Spreadsheet\Helper as SpreadsheetHelper;
use Cyndaron\Ticketsale\Order\Order;
use Cyndaron\Ticketsale\Order\OrderTicketsPage;
use Cyndaron\Ticketsale\Order\OrderTicketTypes;
use Cyndaron\User\UserLevel;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function array_key_exists;
use function array_keys;
use function assert;
use function chr;
use function count;
use function in_array;
use function is_bool;
use function ord;
use function property_exists;

final class ConcertController extends Controller
{
    private const TRANSLATION_MAP = [
        'id' => 'Bestelnr',
        'lastName' => 'Achternaam',
        'initials' => 'Initialen',
        'email' => 'E-mailadres',
        'street' => 'Straat',
        'postcode' => 'Postcode',
        'city' => 'Woonplaats',
        'isPaid' => 'Betaald',
        'comments' => 'Opmerkingen',
        'created' => 'Besteldatum',
        'donor' => 'Donateur',
        'subscribeToNewsletter' => 'Inschrijven voor nieuwsbrief',
    ];

    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getConcertInfo'],
        'order' => ['level' => UserLevel::ANONYMOUS, 'function' => 'order'],
        'orderListExcel' => ['level' => UserLevel::ADMIN, 'function' => 'orderListExcel'],
        'viewOrders' => ['level' => UserLevel::ADMIN, 'function' => 'viewOrders'],
        'viewReservedSeats' => ['level' => UserLevel::ADMIN, 'function' => 'viewReservedSeats'],
    ];

    protected function getConcertInfo(QueryBits $queryBits): JsonResponse
    {
        $concertId = $queryBits->getInt(2);
        if ($concertId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::fetchById($concertId);
        if ($concert === null)
        {
            return new JsonResponse(['error' => 'Concert does not exist!'], Response::HTTP_NOT_FOUND);
        }

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

    protected function order(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new SimplePage('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::fetchById($id);
        if ($concert === null)
        {
            $page = new SimplePage('Fout', 'Concert bestaat niet!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $page = new OrderTicketsPage($concert);
        return new Response($page->render());
    }

    protected function viewOrders(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new SimplePage('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::fetchById($id);
        assert($concert !== null);
        $page = new ConcertOrderOverviewPage($concert);
        return new Response($page->render());
    }

    protected function viewReservedSeats(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new SimplePage('Fout', 'Incorrect ID!');
            return new Response($page->render(), Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::fetchById($id);
        assert($concert !== null);
        $page = new ShowReservedSeats($concert);
        return new Response($page->render());
    }

    protected function orderListExcel(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $concert = Concert::fetchById($id);
        if ($concert === null)
        {
            throw new Exception('Concert niet gevonden!');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $fields = ['id', 'lastName', 'initials', 'email', 'street', 'city', 'isPaid', 'comments'];

        $ticketTypes = TicketType::loadByConcert($concert);
        foreach ($ticketTypes as $ticketType)
        {
            $fields[] = 'Aant. ' . $ticketType->name;
        }

        $orders = Order::loadByConcert($concert);
        foreach ($orders as $order)
        {
            $additionalData = $order->getAdditionalData();
            foreach (array_keys($additionalData) as $additionalDataKey)
            {
                if (!in_array($additionalDataKey, $fields, true))
                {
                    $fields[] = $additionalDataKey;
                }
            }
        }

        $fieldnames = [];
        foreach ($fields as $field)
        {
            $fieldname = $field;
            if (array_key_exists($field, self::TRANSLATION_MAP))
            {
                $fieldname = self::TRANSLATION_MAP[$field];
            }

            $fieldnames[] = $fieldname;
        }

        foreach ($fieldnames as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }
        // Make first row bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach ($orders as $order)
        {
            $column = 'A';
            $additionalData = $order->getAdditionalData();
            $orderTicketTypes = OrderTicketTypes::fetchAll(['orderId = ?'], [$order->id]);
            foreach ($orderTicketTypes as $orderTicketType)
            {
                foreach ($ticketTypes as $ticketType)
                {
                    if ($ticketType->id === $orderTicketType->tickettypeId)
                    {
                        $fieldname = 'Aant. ' . $ticketType->name;
                        if (!array_key_exists($fieldname, $additionalData))
                        {
                            $additionalData[$fieldname] = 0;
                        }

                        $additionalData[$fieldname] += $orderTicketType->amount;
                    }
                }
            }

            foreach ($fields as $field)
            {
                if (property_exists($order, $field))
                {
                    $contents = $order->$field;
                }
                else
                {
                    $contents = $additionalData[$field] ?? '';
                }

                if (is_bool($contents))
                {
                    $contents = ViewHelpers::boolToText($contents);
                }

                $sheet->setCellValue("{$column}{$row}", $contents);
                /** @phpstan-ignore-next-line (you _can_ increment a string that consists of a letter) */
                $column++;
            }

            $row++;
        }
        for ($i = 0, $numHeaders = count($fieldnames); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setAutoSize(true);
        }

        $date = (new DateTime())->format('Y-m-d H.i.s');
        $httpHeaders = SpreadsheetHelper::getResponseHeadersForFilename("Kaartverkoop {$concert->name} (export $date).xlsx");

        return new Response(SpreadsheetHelper::convertToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }
}
