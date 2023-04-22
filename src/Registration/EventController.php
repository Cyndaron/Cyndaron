<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\Request\QueryBits;
use Cyndaron\Routing\Controller;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\User\UserLevel;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use function chr;
use function count;
use function ord;

final class EventController extends Controller
{
    protected array $getRoutes = [
        'getInfo' => ['level' => UserLevel::ANONYMOUS, 'function' => 'getEventInfo'],
        'register' => ['level' => UserLevel::ANONYMOUS, 'function' => 'register'],
        'viewRegistrations' => ['level' => UserLevel::ADMIN, 'function' => 'viewRegistrations'],
        'registrationListExcel' => ['level' => UserLevel::ADMIN, 'function' => 'registrationListExcel'],
    ];

    protected function getEventInfo(QueryBits $queryBits): JsonResponse
    {
        $eventId = $queryBits->getInt(2);
        $event = Event::loadFromDatabase($eventId);
        if ($event === null)
        {
            return new JsonResponse(['error' => 'Event does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $ticketTypes = DBConnection::doQueryAndFetchAll('SELECT * FROM registration_tickettypes WHERE eventId=? ORDER BY price DESC', [$eventId]);

        $answer = [
            'registrationCost0' => $event->registrationCost0,
            'registrationCost1' => $event->registrationCost1,
            'registrationCost2' => $event->registrationCost2,
            'registrationCost3' => $event->registrationCost3,
            'lunchCost' => $event->lunchCost,
            'tickettypes' => $ticketTypes,
        ];

        return new JsonResponse($answer);
    }

    protected function register(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $event = Event::loadFromDatabase($id);
        if ($event === null)
        {
            return new JsonResponse(['error' => 'Event does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $page = new RegistrationPage($event);
        return new Response($page->render());
    }

    protected function viewRegistrations(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $event = Event::loadFromDatabase($id);
        if ($event === null)
        {
            return new JsonResponse(['error' => 'Event does not exist!'], Response::HTTP_NOT_FOUND);
        }
        $page = new EventRegistrationOverviewPage($event);
        return new Response($page->render());
    }

    protected function registrationListExcel(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $event = Event::loadFromDatabase($id);
        if ($event === null)
        {
            throw new Exception('Evenement niet gevonden!');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Achternaam', 'Voornaam', 'Woonplaats', 'E-mailadres', 'Telefoonnummer', 'Leeftijdscategorie', 'Stemsoort', 'Lid van', 'Opmerkingen'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }
        // Make first row bold
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        $row = 2;
        foreach (Registration::loadByEvent($event) as $registration)
        {
            $sheet->setCellValue("A{$row}", $registration->lastName);
            $sheet->setCellValue("B{$row}", $registration->initials);
            $sheet->setCellValue("C{$row}", $registration->city);
            $sheet->setCellValue("D{$row}", $registration->email);
            $sheet->setCellValue("E{$row}", $registration->phone);
            $sheet->setCellValue("F{$row}", $registration->birthYear ? \Cyndaron\Registration\Util::birthYearToCategory($event, $registration->birthYear) : 'Onbekend');
            $sheet->setCellValue("G{$row}", $registration->vocalRange);
            $sheet->setCellValue("H{$row}", $registration->currentChoir ?: 'Geen/ander koor');
            $sheet->setCellValue("I{$row}", $registration->comments);

            $row++;
        }
        for ($i = 0, $numHeaders = count($headers); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setAutoSize(true);
        }

        $date = (new DateTime())->format('Y-m-d H.i.s');
        $httpHeaders = Util::spreadsheetHeadersForFilename("Deelnemers {$event->name} (export $date).xlsx");

        return new Response(ViewHelpers::spreadsheetToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }
}
