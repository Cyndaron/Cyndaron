<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\Connection;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Spreadsheet\Helper as SpreadsheetHelper;
use Cyndaron\Ticketsale\Order\OrderHelper;
use Cyndaron\Ticketsale\Order\OrderRepository;
use Cyndaron\Ticketsale\Order\OrderTicketsPage;
use Cyndaron\Ticketsale\Order\OrderTicketTypes;
use Cyndaron\Ticketsale\Order\OrderTicketTypesRepository;
use Cyndaron\Ticketsale\TicketType\TicketTypeRepository;
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

final class ConcertController
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

    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly ConcertRepository $concertRepository,
    ) {
    }

    #[RouteAttribute('getInfo', RequestMethod::GET, UserLevel::ANONYMOUS, isApiMethod: true)]
    public function getConcertInfo(QueryBits $queryBits, TicketTypeRepository $ticketTypeRepository): JsonResponse
    {
        $concertId = $queryBits->getInt(2);
        if ($concertId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $concert = $this->concertRepository->fetchById($concertId);
        if ($concert === null)
        {
            return new JsonResponse(['error' => 'Concert does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $ticketTypes = $ticketTypeRepository->fetchByConcertAndSortByPrice($concert);

        $answer = [
            'tickettypes' => [],
            'forcedDelivery' => $concert->forcedDelivery,
            'defaultDeliveryCost' => $concert->deliveryCost,
            'reservedSeatCharge' => $concert->reservedSeatCharge,
        ];

        foreach ($ticketTypes as $ticketType)
        {
            $answer['tickettypes'][] = [
                'id' => $ticketType->id,
                'price' => $ticketType->price,
                'discountPer5' => $ticketType->discountPer5,
            ];
        }

        return new JsonResponse($answer);
    }

    #[RouteAttribute('order', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function order(QueryBits $queryBits, TicketTypeRepository $ticketTypeRepository): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new SimplePage('Fout', 'Incorrect ID!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $concert = $this->concertRepository->fetchById($id);
        if ($concert === null)
        {
            $page = new SimplePage('Fout', 'Concert bestaat niet!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $page = new OrderTicketsPage($concert, $ticketTypeRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('viewOrders', RequestMethod::GET, UserLevel::ADMIN)]
    public function viewOrders(QueryBits $queryBits, OrderRepository $orderRepository, Connection $connection, OrderHelper $orderHelper): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            $page = new SimplePage('Fout', 'Incorrect ID!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_BAD_REQUEST);
        }
        $concert = $this->concertRepository->fetchById($id);
        assert($concert !== null);
        $page = new ConcertOrderOverviewPage($concert, $orderRepository, $connection, $orderHelper);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('orderListExcel', RequestMethod::GET, UserLevel::ADMIN)]
    public function orderListExcel(QueryBits $queryBits, OrderRepository $orderRepository, TicketTypeRepository $ticketTypeRepository, OrderTicketTypesRepository $orderTicketTypesRepository): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $concert = $this->concertRepository->fetchById($id);
        if ($concert === null)
        {
            throw new Exception('Concert niet gevonden!');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $fields = ['id', 'lastName', 'initials', 'email', 'street', 'city', 'isPaid', 'comments'];

        $ticketTypes = $ticketTypeRepository->fetchByConcert($concert);
        foreach ($ticketTypes as $ticketType)
        {
            $fields[] = 'Aant. ' . $ticketType->name;
        }

        $orders = $orderRepository->fetchByConcert($concert);
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
            $orderTicketTypes = $orderTicketTypesRepository->fetchAllByOrder($order);
            foreach ($orderTicketTypes as $orderTicketType)
            {
                foreach ($ticketTypes as $ticketType)
                {
                    if ($ticketType->id === $orderTicketType->ticketType->id)
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
