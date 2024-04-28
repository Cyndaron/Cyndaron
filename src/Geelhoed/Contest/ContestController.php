<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\ImproperSubclassing;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Page\Page;
use Cyndaron\Page\SimplePage;
use Cyndaron\Payment\Currency;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Spreadsheet\Helper as SpreadsheetHelper;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserSession;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\TemplateRenderer;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use Mollie\Api\Resources\Payment;
use PhpOffice\PhpSpreadsheet\Shared\Date as PHPSpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use function array_map;
use function assert;
use function basename;
use function chr;
use function count;
use function file_exists;
use function implode;
use function in_array;
use function move_uploaded_file;
use function ord;
use function Safe\date;
use function Safe\error_log;
use function Safe\strtotime;
use function sprintf;
use function time;

final class ContestController extends Controller
{
    /**
     * @throws Exception
     * @return Member[]
     */
    private function fetchMembersByLoggedInUser(): array
    {
        $profile = UserSession::getProfile();
        if ($profile === null)
        {
            return [];
        }

        return Member::fetchAllByUser($profile);
    }

    #[RouteAttribute('overview', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(): Response
    {
        $page = new OverviewPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('view', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function view(QueryBits $queryBits, User|null $currentUser): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            $page = new SimplePage('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $page = new ContestViewPage($contest, $currentUser);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('subscribe', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function subscribe(QueryBits $queryBits, RequestParameters $post): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            $page = new SimplePage('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $memberId = $post->getInt('memberId');
        $member = Member::fetchById($memberId);
        if ($member === null)
        {
            $page = new SimplePage('Onbekend lid', 'Kon het lid niet vinden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }
        $controlledMemberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $this->fetchMembersByLoggedInUser());
        if (!in_array($memberId, $controlledMemberIds, true))
        {
            $page = new SimplePage('Fout', 'U mag dit lid niet beheren.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_FORBIDDEN);
        }

        assert($contest->id !== null);
        assert($member->id !== null);

        if ($member->jbnNumber === '')
        {
            $member->jbnNumber = $post->getAlphaNum('jbnNumber');
            $member->save();
        }

        $contestMember = new ContestMember();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $contestMember->contestId = $contest->id;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $contestMember->memberId = $member->id;
        $contestMember->graduationId = $post->getInt('graduationId');
        $contestMember->weight = $post->getInt('weight');
        $contestMember->comments = $post->getSimpleString('comments');
        $contestMember->isPaid = false;
        if (!$contestMember->save())
        {
            $page = new SimplePage('Fout bij inschrijven', 'Kon de inschrijving niet opslaan!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }

//        // No need to pay, so just redirect.
//        if ($contest->price <= 0.00)
//        {
//            return new RedirectResponse("/contest/view/{$contest->id}");
//        }
//
//        try
//        {
//            $schemeAndHost = "https://{$_SERVER['HTTP_HOST']}";
//            $redirectUrl = "{$schemeAndHost}/contest/view/{$contest->id}";
//            $response = $this->doMollieTransaction([$contestMember], "Inschrijving {$contest->name}", $contest->price, $redirectUrl);
//        }
//        catch (\Exception $e)
//        {
//            UserSession::addNotification('Je inschrijving is opgeslagen, maar de betaling is mislukt!');
//            $response = new RedirectResponse("/contest/view/{$contest->id}");
//        }

//        return $response;
        UserSession::addNotification('Let op: de inschrijving is pas definitief wanneer u heeft betaald.');
        return new RedirectResponse("/contest/view/{$contest->id}");
    }

    /**
     * @param string $schemeAndHost
     * @param ContestMember[] $contestMembers
     * @param string $description
     * @param float $price
     * @param string $redirectUrl
     * @throws ImproperSubclassing
     * @return Response
     */
    private function doMollieTransaction(string $schemeAndHost, array $contestMembers, string $description, float $price, string $redirectUrl): Response
    {
        $webhookUrl = "{$schemeAndHost}/api/contest/mollieWebhook";

        $payment = new \Cyndaron\Payment\Payment($description, $price, Currency::EUR, $redirectUrl, $webhookUrl);
        $molliePayment = $payment->sendToMollie();

        if (empty($molliePayment->id))
        {
            $page = new SimplePage('Fout bij inschrijven', 'Betaling niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        foreach ($contestMembers as $contestMember)
        {
            $contestMember->molliePaymentId = $molliePayment->id;
            if (!$contestMember->save())
            {
                $page = new SimplePage('Fout bij inschrijven', 'Kon de betalings-ID niet opslaan!');
                return $this->pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $redirectUrl = $molliePayment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            UserSession::addNotification('Bedankt voor je inschrijving! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        UserSession::addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

    private function isPartiallyRefunded(Payment $payment): bool
    {
        if (!$payment->isPaid() || !$payment->hasRefunds())
        {
            return false;
        }

        $refundedAmount = $payment->getAmountRefunded();
        if ($refundedAmount === 0.0 || $refundedAmount >= $payment->amount->value)
        {
            return false;
        }

        return true;
    }

    #[RouteAttribute('mollieWebhook', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true)]
    public function mollieWebhook(RequestParameters $post): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $contestMembers = ContestMember::fetchAll(['molliePaymentId = ?'], [$id]);

        if (count($contestMembers) === 0)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' $contestMembers is leeg.';

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }

        $savesSucceeded = true;
        $paidStatus = false;

        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks())
        {
            $paidStatus = true;
        }
        elseif ($this->isPartiallyRefunded($payment))
        {
            $paidStatus = true;
        }

        foreach ($contestMembers as $contestMember)
        {
            $contestMember->isPaid = $paidStatus;
            $savesSucceeded = $savesSucceeded && $contestMember->save();
        }

        if (!$savesSucceeded)
        {
            return new JsonResponse(['error' => 'Could not update payment information for all subscriptions!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('manageOverview', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function manageOverview(TemplateRenderer $templateRenderer): Response
    {
        $page = new Page('Overzicht wedstrijden');
        $page->addScript('/src/Geelhoed/Contest/js/ContestManager.js');
        return $this->pageRenderer->renderResponse($page, ['contents' => PageManagerTabs::contestsTab($templateRenderer)]);
    }

    #[RouteAttribute('subscriptionList', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function subscriptionList(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            return new Response('Kon de wedstrijd niet vinden!', Response::HTTP_NOT_FOUND);
        }
        $page = new SubscriptionListPage($contest);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('subscriptionListExcel', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function subScriptionListExcel(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            throw new Exception('Wedstrijd niet gevonden!');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Naam', 'Geslacht', 'Adres', 'Postcode', 'Woonplaats', 'Geboortedatum', 'Leeftijd', 'Band', 'Gewicht', 'JBN-nummer', 'Betaald', 'Transactie-ID', 'Opmerkingen'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }

        $firstDate = $contest->getFirstDate();
        $row = 2;
        foreach ($contest->getContestMembers() as $contestMember)
        {
            $member = $contestMember->getMember();
            $profile = $member->getProfile();

            $sheet->setCellValue("A{$row}", $profile->getFullName());
            $sheet->setCellValue("B{$row}", $profile->getGenderDisplay());
            $sheet->setCellValue("C{$row}", "{$profile->street} {$profile->houseNumber} {$profile->houseNumberAddition}");
            $sheet->setCellValue("D{$row}", $profile->postalCode);
            $sheet->setCellValue("E{$row}", $profile->city);
            if ($profile->dateOfBirth !== null)
            {
                $dobExcel = PHPSpreadsheetDate::PHPToExcel($profile->dateOfBirth);
                $sheet->setCellValue("F{$row}", $dobExcel);
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            }
            else
            {
                $sheet->setCellValue("F{$row}", '');
            }

            $sheet->setCellValue("G{$row}", $profile->getAge($firstDate));
            $sheet->setCellValue("H{$row}", $contestMember->getGraduation()->name);
            $sheet->setCellValue("I{$row}", $contestMember->weight);
            $sheet->setCellValue("J{$row}", $member->jbnNumber);
            $sheet->setCellValue("K{$row}", ViewHelpers::boolToText($contestMember->isPaid));
            $sheet->setCellValue("L{$row}", $contestMember->molliePaymentId ?? '');
            $sheet->setCellValue("M{$row}", $contestMember->comments);

            $row++;
        }
        for ($i = 0, $numHeaders = count($headers); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setAutoSize(true);
        }

        $date = $firstDate !== null ? $firstDate->format('Y-m-d') : 'onbekende datum';
        $httpHeaders = SpreadsheetHelper::getResponseHeadersForFilename("Deelnemers {$contest->name} ($date).xlsx");

        return new Response(SpreadsheetHelper::convertToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }

    #[RouteAttribute('removeSubscription', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function removeSubscription(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = ContestMember::fetchById($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contestMember->delete();

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function delete(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            return new JsonResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contest->delete();

        return new JsonResponse();
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function createOrEdit(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        if ($id > 0)
        {
            $contest = Contest::fetchById($id);
            if ($contest === null)
            {
                return new JsonResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $contest = new Contest();
        }

        $contest->name = $post->getHTML('name');
        $contest->description = $post->getHTML('description');
        $contest->location = $post->getHTML('location');
        $contest->sportId = $post->getInt('sportId');
        $contest->registrationDeadline = $post->getDate('registrationDeadline');
        $contest->registrationChangeDeadline = $post->getDate('registrationChangeDeadline');
        $contest->price = $post->getFloat('price');
        if (!$contest->save())
        {
            return new JsonResponse(['error' => 'Could not save contest!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('updatePaymentStatus', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function updatePaymentStatus(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = ContestMember::fetchById($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contestMember->isPaid = $post->getBool('isPaid');
        $contestMember->save();

        return new JsonResponse();
    }

    #[RouteAttribute('contestantsList', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsList(): Response
    {
        $page = new ContestantsListPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('contestantsEmail', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsEmail(): Response
    {
        $page = new ContestantsEmailPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('contestantsListExcel', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsListExcel(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Naam', 'Geslacht', 'Adres', 'Postcode', 'Woonplaats', 'Geboortedatum', 'Banden', 'JBN-nummer'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }

        $contestants = Member::fetchAllAndSortByName(['isContestant = 1']);
        $sports = Sport::fetchAll();
        $row = 2;
        foreach ($contestants as $member)
        {
            $profile = $member->getProfile();

            $sheet->setCellValue("A{$row}", $profile->getFullName());
            $sheet->setCellValue("B{$row}", $profile->getGenderDisplay());
            $sheet->setCellValue("C{$row}", "{$profile->street} {$profile->houseNumber} {$profile->houseNumberAddition}");
            $sheet->setCellValue("D{$row}", $profile->postalCode);
            $sheet->setCellValue("E{$row}", $profile->city);
            if ($profile->dateOfBirth !== null)
            {
                $dobExcel = PHPSpreadsheetDate::PHPToExcel($profile->dateOfBirth);
                $sheet->setCellValue("F{$row}", $dobExcel);
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            }
            else
            {
                $sheet->setCellValue("F{$row}", '');
            }
            $graduations = [];
            foreach ($sports as $sport)
            {
                $highest = $member->getHighestGraduation($sport);
                if ($highest !== null)
                {
                    $graduations[] = "{$sport->name}: {$highest->name}";
                }
            }
            $sheet->setCellValue("G{$row}", implode("\r\n", $graduations));
            $sheet->setCellValue("H{$row}", $member->jbnNumber);

            $row++;
        }
        for ($i = 0, $numHeaders = count($headers); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setAutoSize(true);
        }

        $date = date('Y-m-d');
        $httpHeaders = SpreadsheetHelper::getResponseHeadersForFilename("Wedstrijdjudoka's (uitvoer {$date}).xlsx");

        return new Response(SpreadsheetHelper::convertToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }

    #[RouteAttribute('myContests', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function myContests(User $currentUser): Response
    {
        $page = new MyContestsPage($currentUser);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('payFullDue', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function payFullDue(UrlInfo $urlInfo, User $currentUser): Response
    {
        [$due, $contestMembers] = Contest::getTotalDue($currentUser);
        if ($due === 0.00)
        {
            return new Response('Er staan geen betalingen open.');
        }

        $contests = [];
        foreach ($contestMembers as $contestMember)
        {
            $contest = $contestMember->getContest();
            $contests[$contest->id] = $contest;
        }
        $contestNames = array_map(static function(Contest $contest)
        {
            return $contest->name;
        }, $contests);

        try
        {
            $redirectUrl = "{$urlInfo->schemeAndHost}/contest/myContests";
            $description = implode(' + ', $contestNames) . ' - Geelhoed';
            $response = $this->doMollieTransaction($urlInfo->schemeAndHost, $contestMembers, $description, $due, $redirectUrl);
        }
        catch (Exception $e)
        {
            UserSession::addNotification('De betaling is mislukt!');
            $response = new RedirectResponse("/contest/myContests");
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->getMessage());
        }

        return $response;
    }

    #[RouteAttribute('addAttachment', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function addAttachment(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            return new Response('Wedstrijd bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dir = Util::UPLOAD_DIR . '/contest/' . $contest->id . '/attachments';
        Util::ensureDirectoryExists($dir);

        $filteredParams = new RequestParameters($_FILES['newFile']);
        $filename = $dir . '/' . basename($filteredParams->getFilename('name'));
        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            UserSession::addNotification('Bijlage geüpload');
        }
        else
        {
            UserSession::addNotification('Bijlage kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/contest/view/' . $contest->id);
    }

    #[RouteAttribute('deleteAttachment', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function deleteAttachment(QueryBits $queryBits, RequestParameters $post): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($id);
        if ($contest === null)
        {
            return new Response('Wedstrijd bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dir = Util::UPLOAD_DIR . '/contest/' . $contest->id . '/attachments';
        $filename = $post->getFilename('filename');
        $fullPath = "$dir/$filename";
        if (file_exists($fullPath))
        {
            if (Util::deleteFile($fullPath))
            {
                UserSession::addNotification('Bestand verwijderd.');
            }
            else
            {
                UserSession::addNotification('Bestand kon niet worden verwijderd.');
            }
        }
        else
        {
            UserSession::addNotification('Bestand bestaat niet.');
        }

        return new RedirectResponse('/contest/view/' . $contest->id);
    }

    #[RouteAttribute('addDate', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function addDate(RequestParameters $post): JsonResponse
    {
        $contestId = $post->getInt('contestId');
        if ($contestId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::fetchById($contestId);
        if ($contest === null)
        {
            return new JsonResponse(['error' => 'Wedstrijd bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $startDateTime = $post->getDate('date') . ' ' . $post->getDate('startTime') . ':00';
        $endDateTime = $post->getDate('date') . ' ' . $post->getDate('endTime') . ':00';
        $contestDate = new ContestDate();
        $contestDate->contestId = $contestId;
        $contestDate->start =  DateTime::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $startDateTime);
        $contestDate->end =  DateTime::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $endDateTime);
        $contestDate->save();

        $contestDateId = $contestDate->id;
        if ($contestDateId === null)
        {
            return new JsonResponse(['error' => 'Kon de datum niet opslaan!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $classes = ContestClass::fetchAll();
        foreach ($classes as $class)
        {
            if ($post->getBool('class-' . $class->id))
            {
                $contestDate->addClass($class);
            }
        }

        return new JsonResponse(['status' => 'ok']);
    }

    #[RouteAttribute('deleteDate', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function deleteDate(QueryBits $queryBits): Response
    {
        $contestDateId = $queryBits->getInt(2);
        if ($contestDateId < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contestDate = ContestDate::fetchById($contestDateId);
        if ($contestDate === null)
        {
            return new Response('Wedstrijddatum bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $contest = $contestDate->getContest();
        $contestDate->delete();
        return new RedirectResponse('/contest/view/' . $contest->id);
    }

    #[RouteAttribute('editSubscription', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function editSubscription(QueryBits $queryBits, RequestParameters $post, UrlInfo $urlInfo, User|null $currentUser): Response
    {
        $id = $queryBits->getInt(2);
        $subscription = ContestMember::fetchById($id);
        if ($subscription === null)
        {
            return new Response('Record bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        if ($currentUser === null)
        {
            return new Response('U moet ingelogd zijn!', Response::HTTP_UNAUTHORIZED);
        }
        if (!$currentUser->hasRight(Contest::RIGHT_MANAGE))
        {
            $memberId = $subscription->getMember()->id;
            $controlledMemberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $this->fetchMembersByLoggedInUser());
            if (!in_array($memberId, $controlledMemberIds, true))
            {
                return new Response('U mag deze judoka niet beheren!', Response::HTTP_FORBIDDEN);
            }
        }

        if (!$subscription->canBeChanged($currentUser))
        {
            return new Response('De deadline voor aanpassingen is verlopen!', Response::HTTP_BAD_REQUEST);
        }

        $subscription->weight = $post->getInt('weight');
        $subscription->graduationId = $post->getInt('graduationId');
        if ($subscription->save())
        {
            UserSession::addNotification('Wijzigingen opgeslagen.');
            // Since we only start entering names and data once people have paid, no need to notify for changes if they haven't paid yet.
            if ($subscription->isPaid)
            {
                $mailText = "{$subscription->getMember()->getProfile()->getFullName()} heeft zijn/haar inschrijving voor {$subscription->getContest()->name} gewijzigd. Het gewicht is nu {$subscription->weight} kg en de graduatie is: {$subscription->getGraduation()->name}.";
                $to = Setting::get('geelhoed_contestMaintainerMail');
                $mail = UtilMail::createMailWithDefaults($urlInfo->domain, new Address($to), 'Wijziging inschrijving', $mailText);
                $mail->send();
            }
        }

        return new RedirectResponse('/contest/myContests');
    }

    #[RouteAttribute('editSubscription', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function editSubscriptionPage(QueryBits $queryBits, User|null $currentUser): Response
    {
        $id = $queryBits->getInt(2);
        $subscription = ContestMember::fetchById($id);
        if ($subscription === null)
        {
            return new Response('Record bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        if ($currentUser === null)
        {
            return new Response('U moet ingelogd zijn!', Response::HTTP_UNAUTHORIZED);
        }
        if (!$currentUser->hasRight(Contest::RIGHT_MANAGE))
        {
            $memberId = $subscription->getMember()->id;
            $controlledMemberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $this->fetchMembersByLoggedInUser());
            if (!in_array($memberId, $controlledMemberIds, true))
            {
                return new Response('U mag deze judoka niet beheren!', Response::HTTP_FORBIDDEN);
            }
        }

        if (!$subscription->canBeChanged($currentUser))
        {
            return new Response('De deadline voor aanpassingen is verlopen!', Response::HTTP_BAD_REQUEST);
        }

        $page = new EditSubscriptionPage($subscription);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('parentAccounts', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function parentAccounts(): Response
    {
        $page = new ParentAccountsPage();
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('linkContestantsToParentAccounts', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
        public function linkContestantsToParentAccounts(): Response
        {
            $page = new LinkContestantsToParentAccountsPage();
            return $this->pageRenderer->renderResponse($page);
        }

    #[RouteAttribute('subscribe', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function subscribePage(QueryBits $queryBits, User $currentUser): Response
    {
        $contestId = $queryBits->getInt(2);
        $memberId = $queryBits->getInt(3);

        $controlledMembers = Member::fetchAllContestantsByUser($currentUser);
        $memberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $controlledMembers);

        if (!in_array($memberId, $memberIds, true))
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'U kunt deze judoka niet beheren!'), status:  Response::HTTP_BAD_REQUEST);
        }
        $member = Member::fetchById($memberId);
        if ($member === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Lid niet gevonden!'), status:  Response::HTTP_NOT_FOUND);
        }

        $contest = Contest::fetchById($contestId);
        if ($contest === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Wedstrijd niet gevonden!'), status:  Response::HTTP_NOT_FOUND);
        }

        $contestMember = ContestMember::fetchByContestAndMember($contest, $member);
        if ($contestMember !== null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Deze judoka is al ingeschreven!'), status:  Response::HTTP_NOT_FOUND);
        }

        if (strtotime($contest->registrationDeadline) < time())
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Voor deze wedstrijd kan niet meer worden ingeschreven!'), status:  Response::HTTP_BAD_REQUEST);
        }

        return $this->pageRenderer->renderResponse(new SubscribePage($contest, $member));
    }

    #[RouteAttribute('createParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function createParentAccount(RequestParameters $post, UrlInfo $urlInfo): JsonResponse
    {
        $user = new User();
        $user->firstName = $post->getSimpleString('firstName');
        $user->initials = $post->getSimpleString('initials');
        $user->tussenvoegsel = $post->getSimpleString('tussenvoegsel');
        $user->lastName = $post->getSimpleString('lastName');
        $user->email = $post->getEmail('email');

        try
        {
            if (!$user->save())
            {
                return new JsonResponse(['error' => 'Kon ouderaccount niet opslaan'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
            $user->addRight(Contest::RIGHT_PARENT);
        }
        catch (\PDOException)
        {
            return new JsonResponse(['error' => 'Kon ouderaccount niet opslaan, databasefout. Controleer of het e-mailadres uniek is.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }


        if ($post->getBool('sendIntroductionMail'))
        {
            if (!$this->sendParentAccountIntroductionMail($user, $urlInfo->domain))
            {
                return new JsonResponse(['error' => 'Account is aangemaakt, maar kon welkomstmail niet versturen'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        UserSession::addNotification('Ouderaccount aangemaakt.');
        return new JsonResponse();
    }

    public function sendParentAccountIntroductionMail(User $user, string $domain): bool
    {
        $password = $user->generatePassword();
        $user->save();

        $mailBody = $this->templateRenderer->render('Geelhoed/Contest/ParentAccountIntroductionMail', [
            'fullName' => $user->getFullName(),
            'email' => $user->email,
            'password' => $password,
        ]);

        assert($user->email !== null);
        $mail = UtilMail::createMailWithDefaults($domain, new Address($user->email), 'Ouderaccount aangemaakt', $mailBody);
        return $mail->send();
    }

    #[RouteAttribute('deleteParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function deleteParentAccount(QueryBits $queryBits): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $user = User::fetchById($id);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }
        if (!$user->hasRight(Contest::RIGHT_PARENT))
        {
            return new JsonResponse(['error' => 'Gebruiker is geen ouderaccount!'], Response::HTTP_BAD_REQUEST);
        }

        $user->delete();
        return new JsonResponse();
    }

    #[RouteAttribute('deleteFromParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function deleteFromParentAccount(RequestParameters $post, Connection $db): JsonResponse
    {
        $userId = $post->getInt('userId');
        $user = User::fetchById($userId);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $memberToRemoveId = $post->getInt('memberId');
        $controlledMembers = Member::fetchAllByUser($user);
        if (!in_array($memberToRemoveId, array_map(static function(Member $member)
        {
            return $member->id;
        }, $controlledMembers), true))
        {
            return new JsonResponse(['error' => 'Ouder kan dit lid niet beheren!'], Response::HTTP_NOT_FOUND);
        }

        $db->executeQuery('DELETE FROM geelhoed_users_members WHERE userId = ? AND memberId = ?', [$userId, $memberToRemoveId]);
        return new JsonResponse();
    }

    #[RouteAttribute('addToParentAccount', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function addToParentAccount(RequestParameters $post, Connection $db): Response
    {
        $userId = $post->getInt('userId');
        $user = User::fetchById($userId);
        if ($user === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Gebruiker bestaat niet!'), status:  Response::HTTP_NOT_FOUND);
        }

        $memberId = $post->getInt('memberId');
        $member = Member::fetchById($memberId);
        if ($member === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Lid bestaat niet!'), status:  Response::HTTP_NOT_FOUND);
        }

        $db->executeQuery('INSERT INTO geelhoed_users_members(`userId`, `memberId`) VALUES(?, ?)', [$userId, $memberId]);
        return new RedirectResponse('/contest/parentAccounts');
    }

    #[RouteAttribute('cancelSubscription', RequestMethod::POST, UserLevel::LOGGED_IN, isApiMethod: true)]
    public function cancelSubscription(QueryBits $queryBits, User $currentUser): JsonResponse
    {
        $contestMemberId = $queryBits->getInt(2);
        $contestMember = ContestMember::fetchById($contestMemberId);
        if ($contestMember === null)
        {
            return new JsonResponse(['message' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $member = $contestMember->getMember();
        $manageableContestants = Member::fetchAllContestantsByUser($currentUser);
        $canManage = false;
        foreach ($manageableContestants as $manageableContestant)
        {
            if ($manageableContestant->id === $member->id)
            {
                $canManage = true;
                break;
            }
        }

        if (!$canManage)
        {
            return new JsonResponse(['message' => 'U kunt deze gebruiker niet beheren!'], Response::HTTP_FORBIDDEN);
        }

        if ($contestMember->isPaid)
        {
            return new JsonResponse(['message' => 'U kunt niet meer annuleren als er al betaald is!'], Response::HTTP_BAD_REQUEST);
        }

        $contestMember->delete();

        return new JsonResponse(['message' => 'De inschrijving is geannuleerd!']);
    }
}
