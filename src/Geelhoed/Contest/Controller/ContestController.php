<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Controller;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Geelhoed\Contest\Model\Contest;
use Cyndaron\Geelhoed\Contest\Model\ContestClassRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestDate;
use Cyndaron\Geelhoed\Contest\Model\ContestDateRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestMember;
use Cyndaron\Geelhoed\Contest\Model\ContestMemberRepository;
use Cyndaron\Geelhoed\Contest\Model\ContestRepository;
use Cyndaron\Geelhoed\Contest\Page\ContestantsEmailPage;
use Cyndaron\Geelhoed\Contest\Page\ContestantsListPage;
use Cyndaron\Geelhoed\Contest\Page\ContestViewPage;
use Cyndaron\Geelhoed\Contest\Page\EditSubscriptionPage;
use Cyndaron\Geelhoed\Contest\Page\LinkContestantsToParentAccountsPage;
use Cyndaron\Geelhoed\Contest\Page\MyContestsPage;
use Cyndaron\Geelhoed\Contest\Page\OverviewPage;
use Cyndaron\Geelhoed\Contest\Page\ParentAccountsPage;
use Cyndaron\Geelhoed\Contest\Page\SubscribePage;
use Cyndaron\Geelhoed\Contest\Page\SubscriptionListPage;
use Cyndaron\Geelhoed\Graduation\Graduation;
use Cyndaron\Geelhoed\Graduation\GraduationRepository;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\Geelhoed\Sport\SportRepository;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\Payment\Currency;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\Spreadsheet\Helper as SpreadsheetHelper;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\User\UserRepository;
use Cyndaron\User\UserSession;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\TemplateRenderer;
use Cyndaron\View\Template\ViewHelpers;
use Exception;
use Illuminate\Http\Request as HttpRequest;
use Mollie\Api\Resources\Payment;
use PhpOffice\PhpSpreadsheet\Shared\Date as PHPSpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Psr\Log\LoggerInterface;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
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
use function ord;
use function Safe\date;
use function Safe\error_log;
use function Safe\preg_replace;
use function Safe\strtotime;
use function sprintf;
use function time;

final class ContestController
{
    public function __construct(
        private readonly PageRenderer $pageRenderer,
        private readonly TemplateRenderer $templateRenderer,
        private readonly ContestRepository $contestRepository,
        private readonly ContestDateRepository $contestDateRepository,
        private readonly ContestMemberRepository $contestMemberRepository,
        private readonly MemberRepository $memberRepository,
        private readonly SettingsRepository $settingsRepository,
    ) {
    }

    /**
     * @throws Exception
     * @return Member[]
     */
    private function fetchMembersByLoggedInUser(UserSession $userSession): array
    {
        $profile = $userSession->getProfile();
        if ($profile === null)
        {
            return [];
        }

        return $this->memberRepository->fetchAllByUser($profile);
    }

    #[RouteAttribute('overview', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function overview(): Response
    {
        $page = new OverviewPage($this->contestRepository, $this->contestDateRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('view', RequestMethod::GET, UserLevel::ANONYMOUS)]
    public function view(QueryBits $queryBits, ContestViewPage $contestViewPage): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($id);
        if ($contest === null)
        {
            $page = new SimplePage('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        return $this->pageRenderer->renderResponse($contestViewPage->createPage($contest));
    }

    #[RouteAttribute('subscribe', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function subscribe(QueryBits $queryBits, RequestParameters $post, UserSession $userSession, GraduationRepository $graduationRepository): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($id);
        if ($contest === null)
        {
            $page = new SimplePage('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        $memberId = $post->getInt('memberId');
        $member = $this->memberRepository->fetchById($memberId);
        if ($member === null)
        {
            $page = new SimplePage('Onbekend lid', 'Kon het lid niet vinden.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }
        $controlledMemberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $this->fetchMembersByLoggedInUser($userSession));
        if (!in_array($memberId, $controlledMemberIds, true))
        {
            $page = new SimplePage('Fout', 'U mag dit lid niet beheren.');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_FORBIDDEN);
        }

        $graduation = $graduationRepository->fetchById($post->getInt('graduationId'));
        if ($graduation === null)
        {
            $page = new SimplePage('Fout', 'Ongeldige band/kyu');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_FORBIDDEN);
        }

        assert($contest->id !== null);
        assert($member->id !== null);

        if ($member->jbnNumber === '')
        {
            $member->jbnNumber = $post->getAlphaNum('jbnNumber');
            $this->memberRepository->save($member);
        }

        $contestMember = new ContestMember();
        $contestMember->contest = $contest;
        $contestMember->member = $member;
        $contestMember->graduation = $graduation;
        $contestMember->weight = $post->getInt('weight');
        $contestMember->comments = $post->getSimpleString('comments');
        $contestMember->isPaid = false;
        $this->contestMemberRepository->save($contestMember);

        $userSession->addNotification('Let op: de inschrijving is pas definitief wanneer u heeft betaald.');
        return new RedirectResponse("/contest/view/{$contest->id}");
    }

    /**
     * @param UserSession $userSession
     * @param string $schemeAndHost
     * @param ContestMember[] $contestMembers
     * @param string $description
     * @param float $price
     * @param string $redirectUrl
     * @return Response
     */
    private function doMollieTransaction(UserSession $userSession, string $schemeAndHost, array $contestMembers, string $description, float $price, string $redirectUrl): Response
    {
        $webhookUrl = "{$schemeAndHost}/api/contest/mollieWebhook";

        $apiKey = $this->settingsRepository->get('geelhoed_contestMollieApiKey');
        $payment = new \Cyndaron\Payment\Payment($description, $price, Currency::EUR, $redirectUrl, $webhookUrl, $apiKey);
        $molliePayment = $payment->sendToMollie();

        if (empty($molliePayment->id))
        {
            $page = new SimplePage('Fout bij inschrijven', 'Betaling niet gevonden!');
            return $this->pageRenderer->renderResponse($page, status: Response::HTTP_NOT_FOUND);
        }

        foreach ($contestMembers as $contestMember)
        {
            $contestMember->molliePaymentId = $molliePayment->id;
            $this->contestMemberRepository->save($contestMember);
        }

        $redirectUrl = $molliePayment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            $userSession->addNotification('Bedankt voor je inschrijving! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        $userSession->addNotification('Bedankt voor de betaling! Het kan even duren voordat deze geregistreerd is.');
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

    #[RouteAttribute('mollieWebhook', RequestMethod::POST, UserLevel::ANONYMOUS, isApiMethod: true, skipCSRFCheck: true)]
    public function mollieWebhook(RequestParameters $post): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $contestMembers = $this->contestMemberRepository->fetchAll(['molliePaymentId = ?'], [$id]);

        if (count($contestMembers) === 0)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' $contestMembers is leeg.';

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }

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
            $this->contestMemberRepository->save($contestMember);
        }

        return new JsonResponse();
    }

    #[RouteAttribute('manageOverview', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function manageOverview(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, SportRepository $sportRepository): Response
    {
        $page = new Page();
        $page->title = 'Overzicht wedstrijden';
        $page->addScript('/src/Geelhoed/Contest/js/ContestManager.js');
        return $this->pageRenderer->renderResponse(
            $page,
            [
                'contents' => PageManagerTabs::contestsTab($templateRenderer, $tokenHandler, $this->contestRepository, $this->contestDateRepository, $sportRepository)
            ]
        );
    }

    #[RouteAttribute('subscriptionList', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function subscriptionList(QueryBits $queryBits): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($id);
        if ($contest === null)
        {
            return new Response('Kon de wedstrijd niet vinden!', Response::HTTP_NOT_FOUND);
        }
        $page = new SubscriptionListPage($contest, $this->contestDateRepository, $this->contestMemberRepository);
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
        $contest = $this->contestRepository->fetchById($id);
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

        $firstDate = $this->contestDateRepository->getFirstByContest($contest);
        $row = 2;
        foreach ($this->contestMemberRepository->fetchAllByContest($contest) as $contestMember)
        {
            $member = $contestMember->member;
            $profile = $member->profile;

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
            $sheet->setCellValue("H{$row}", $contestMember->graduation->name);
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
    public function removeSubscription(RequestParameters $post, GenericRepository $repository): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = $this->contestMemberRepository->fetchById($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $repository->delete($contestMember);

        return new JsonResponse();
    }

    #[RouteAttribute('delete', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function delete(RequestParameters $post, GenericRepository $repository): JsonResponse
    {
        $id = $post->getInt('id');
        $contest = $this->contestRepository->fetchById($id);
        if ($contest === null)
        {
            return new JsonResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $repository->delete($contest);

        return new JsonResponse();
    }

    #[RouteAttribute('edit', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function createOrEdit(RequestParameters $post, SportRepository $sportRepository): JsonResponse
    {
        $sport = $sportRepository->fetchById($post->getInt('sportId'));
        if ($sport === null)
        {
            return new JsonResponse(['error' => 'Sport does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $id = $post->getInt('id');
        if ($id > 0)
        {
            $contest = $this->contestRepository->fetchById($id);
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
        $contest->sport = $sport;
        $contest->registrationDeadline = $post->getDate('registrationDeadline');
        $contest->registrationChangeDeadline = $post->getDate('registrationChangeDeadline');
        $contest->price = $post->getFloat('price');
        $this->contestRepository->save($contest);

        return new JsonResponse();
    }

    #[RouteAttribute('updatePaymentStatus', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function updatePaymentStatus(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = $this->contestMemberRepository->fetchById($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contestMember->isPaid = $post->getBool('isPaid');
        $this->contestMemberRepository->save($contestMember);

        return new JsonResponse();
    }

    #[RouteAttribute('contestantsList', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsList(SportRepository $sportRepository): Response
    {
        $page = new ContestantsListPage($this->memberRepository, $sportRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('contestantsEmail', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsEmail(UserRepository $userRepository): Response
    {
        $page = new ContestantsEmailPage($this->memberRepository, $userRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('contestantsListExcel', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function contestantsListExcel(SportRepository $sportRepository): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Naam', 'Geslacht', 'Adres', 'Postcode', 'Woonplaats', 'Geboortedatum', 'Banden', 'JBN-nummer'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }

        $contestants = $this->memberRepository->fetchAllAndSortByName(['isContestant = 1']);
        $sports = $sportRepository->fetchAll();
        $row = 2;
        foreach ($contestants as $member)
        {
            $profile = $member->profile;

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
                $highest = $this->memberRepository->getHighestGraduation($member, $sport);
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
    public function myContests(MyContestsPage $myContestsPage): Response
    {
        return $this->pageRenderer->renderResponse($myContestsPage->createPage());
    }

    #[RouteAttribute('payFullDue', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function payFullDue(UrlInfo $urlInfo, User $currentUser, UserSession $userSession, LoggerInterface $logger): Response
    {
        [$due, $contestMembers] = $this->contestRepository->getTotalDue($currentUser, $this->memberRepository);
        if ($due === 0.00)
        {
            return new Response('Er staan geen betalingen open.');
        }

        $contests = [];
        foreach ($contestMembers as $contestMember)
        {
            $contest = $contestMember->contest;
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
            $response = $this->doMollieTransaction($userSession, $urlInfo->schemeAndHost, $contestMembers, $description, $due, $redirectUrl);
        }
        catch (Exception $e)
        {
            $userSession->addNotification('De betaling is mislukt!');
            $response = new RedirectResponse("/contest/myContests");
            $logger->error($e->getMessage());
        }

        return $response;
    }

    #[RouteAttribute('addAttachment', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function addAttachment(QueryBits $queryBits, Request $request, UserSession $userSession): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($id);
        if ($contest === null)
        {
            return new Response('Wedstrijd bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dir = Util::UPLOAD_DIR . '/contest/' . $contest->id . '/attachments';
        Util::ensureDirectoryExists($dir);

        $file = $request->files->get('newFile');
        assert($file instanceof UploadedFile);
        /** @var string $newFilename */
        $newFilename = preg_replace('/[^A-Za-z0-9() \-+.]/', '', basename($file->getClientOriginalName()));

        try
        {
            $file->move($dir, $newFilename);
            $userSession->addNotification('Bijlage geüpload');
        }
        catch (FileException)
        {
            $userSession->addNotification('Bijlage kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/contest/view/' . $contest->id);
    }

    #[RouteAttribute('deleteAttachment', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function deleteAttachment(QueryBits $queryBits, RequestParameters $post, UserSession $userSession): Response
    {
        $id = $queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($id);
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
                $userSession->addNotification('Bestand verwijderd.');
            }
            else
            {
                $userSession->addNotification('Bestand kon niet worden verwijderd.');
            }
        }
        else
        {
            $userSession->addNotification('Bestand bestaat niet.');
        }

        return new RedirectResponse('/contest/view/' . $contest->id);
    }

    #[RouteAttribute('addDate', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function addDate(RequestParameters $post, ContestDateRepository $repository, ContestClassRepository $contestClassRepository): JsonResponse
    {
        $contestId = $post->getInt('contestId');
        if ($contestId < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = $this->contestRepository->fetchById($contestId);
        if ($contest === null)
        {
            return new JsonResponse(['error' => 'Wedstrijd bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $startDateTime = $post->getDate('date') . ' ' . $post->getDate('startTime') . ':00';
        $endDateTime = $post->getDate('date') . ' ' . $post->getDate('endTime') . ':00';
        $contestDate = new ContestDate();
        $contestDate->contest = $contest;
        $contestDate->start =  DateTime::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $startDateTime);
        $contestDate->end =  DateTime::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $endDateTime);
        $repository->save($contestDate);

        $contestDateId = $contestDate->id;
        if ($contestDateId === null)
        {
            return new JsonResponse(['error' => 'Kon de datum niet opslaan!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $classes = $contestClassRepository->fetchAll();
        foreach ($classes as $class)
        {
            if ($post->getBool('class-' . $class->id))
            {
                $repository->addClass($contestDate, $class);
            }
        }

        return new JsonResponse(['status' => 'ok']);
    }

    #[RouteAttribute('deleteDate', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function deleteDate(QueryBits $queryBits, ContestDateRepository $repository): Response
    {
        $contestDateId = $queryBits->getInt(2);
        if ($contestDateId < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contestDate = $repository->fetchById($contestDateId);
        if ($contestDate === null)
        {
            return new Response('Wedstrijddatum bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $repository->delete($contestDate);
        return new RedirectResponse('/contest/view/' . $contestDate->contest->id);
    }

    #[RouteAttribute('editSubscription', RequestMethod::POST, UserLevel::LOGGED_IN)]
    public function editSubscription(QueryBits $queryBits, RequestParameters $post, UserSession $userSession, UserRepository $repository, MailFactory $mailFactory, GraduationRepository $graduationRepository): Response
    {
        $id = $queryBits->getInt(2);
        $contestMember = $this->contestMemberRepository->fetchById($id);
        if ($contestMember === null)
        {
            return new Response('Record bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $graduation = $graduationRepository->fetchById($post->getInt('graduationId'));
        if ($graduation === null)
        {
            return new Response('Band/graduatie bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $currentUser = $userSession->getProfile();
        if ($currentUser === null)
        {
            return new Response('U moet ingelogd zijn!', Response::HTTP_UNAUTHORIZED);
        }
        if (!$repository->userHasRight($currentUser, Contest::RIGHT_MANAGE))
        {
            $memberId = $contestMember->member->id;
            $controlledMemberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $this->fetchMembersByLoggedInUser($userSession));
            if (!in_array($memberId, $controlledMemberIds, true))
            {
                return new Response('U mag deze judoka niet beheren!', Response::HTTP_FORBIDDEN);
            }
        }

        if (!$this->contestRepository->registrationCanBeChanged($contestMember->contest, $currentUser))
        {
            return new Response('De deadline voor aanpassingen is verlopen!', Response::HTTP_BAD_REQUEST);
        }

        $contestMember->weight = $post->getInt('weight');
        $contestMember->graduation = $graduation;
        $this->contestMemberRepository->save($contestMember);

        $userSession->addNotification('Wijzigingen opgeslagen.');
        // Since we only start entering names and data once people have paid, no need to notify for changes if they haven't paid yet.
        if ($contestMember->isPaid)
        {
            $mailText = "{$contestMember->member->profile->getFullName()} heeft zijn/haar inschrijving voor {$contestMember->contest->name} gewijzigd. Het gewicht is nu {$contestMember->weight} kg en de graduatie is: {$contestMember->graduation->name}.";
            $to = Setting::get('geelhoed_contestMaintainerMail');
            $mail = $mailFactory->createMailWithDefaults(new Address($to), 'Wijziging inschrijving', $mailText);
            $mail->send();
        }

        return new RedirectResponse('/contest/myContests');
    }

    #[RouteAttribute('editSubscription', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function editSubscriptionPage(QueryBits $queryBits, UserSession $userSession, GraduationRepository $graduationRepository, UserRepository $userRepository): Response
    {
        $id = $queryBits->getInt(2);
        $contestMember = $this->contestMemberRepository->fetchById($id);
        if ($contestMember === null)
        {
            return new Response('Record bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $currentUser = $userSession->getProfile();
        if ($currentUser === null)
        {
            return new Response('U moet ingelogd zijn!', Response::HTTP_UNAUTHORIZED);
        }
        if (!$userRepository->userHasRight($currentUser, Contest::RIGHT_MANAGE))
        {
            $memberId = $contestMember->member->id;
            $controlledMemberIds = array_map(static function(Member $member)
            {
                return $member->id;
            }, $this->fetchMembersByLoggedInUser($userSession));
            if (!in_array($memberId, $controlledMemberIds, true))
            {
                return new Response('U mag deze judoka niet beheren!', Response::HTTP_FORBIDDEN);
            }
        }

        if (!$this->contestRepository->registrationCanBeChanged($contestMember->contest, $currentUser))
        {
            return new Response('De deadline voor aanpassingen is verlopen!', Response::HTTP_BAD_REQUEST);
        }

        $page = new EditSubscriptionPage($contestMember, $graduationRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('parentAccounts', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function parentAccounts(UserRepository $userRepository): Response
    {
        $page = new ParentAccountsPage($userRepository, $this->memberRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('linkContestantsToParentAccounts', RequestMethod::GET, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function linkContestantsToParentAccounts(UserRepository $userRepository): Response
    {
        $page = new LinkContestantsToParentAccountsPage($this->memberRepository, $userRepository);
        return $this->pageRenderer->renderResponse($page);
    }

    #[RouteAttribute('subscribe', RequestMethod::GET, UserLevel::LOGGED_IN)]
    public function subscribePage(QueryBits $queryBits, User $currentUser, GraduationRepository $graduationRepository): Response
    {
        $contestId = $queryBits->getInt(2);
        $memberId = $queryBits->getInt(3);

        $controlledMembers = $this->memberRepository->fetchAllContestantsByUser($currentUser);
        $memberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $controlledMembers);

        if (!in_array($memberId, $memberIds, true))
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'U kunt deze judoka niet beheren!'), status:  Response::HTTP_BAD_REQUEST);
        }
        $member = $this->memberRepository->fetchById($memberId);
        if ($member === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Lid niet gevonden!'), status:  Response::HTTP_NOT_FOUND);
        }

        $contest = $this->contestRepository->fetchById($contestId);
        if ($contest === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Wedstrijd niet gevonden!'), status:  Response::HTTP_NOT_FOUND);
        }

        $contestMember = $this->contestMemberRepository->fetchByContestAndMember($contest, $member);
        if ($contestMember !== null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Deze judoka is al ingeschreven!'), status:  Response::HTTP_NOT_FOUND);
        }

        if (strtotime($contest->registrationDeadline) < time())
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Voor deze wedstrijd kan niet meer worden ingeschreven!'), status:  Response::HTTP_BAD_REQUEST);
        }

        $subscribePage = new SubscribePage($contest, $member, $graduationRepository, $this->memberRepository);
        return $this->pageRenderer->renderResponse($subscribePage);
    }

    #[RouteAttribute('createParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function createParentAccount(RequestParameters $post, MailFactory $mailFactory, UserSession $userSession, UserRepository $userRepository): JsonResponse
    {
        $user = new User();
        $user->firstName = $post->getSimpleString('firstName');
        $user->initials = $post->getSimpleString('initials');
        $user->tussenvoegsel = $post->getSimpleString('tussenvoegsel');
        $user->lastName = $post->getSimpleString('lastName');
        $user->email = $post->getEmail('email');

        try
        {
            $userRepository->save($user);
            $userRepository->addRightToUser($user, Contest::RIGHT_PARENT);
        }
        catch (\PDOException)
        {
            return new JsonResponse(['error' => 'Kon ouderaccount niet opslaan, databasefout. Controleer of het e-mailadres uniek is.'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if ($post->getBool('sendIntroductionMail'))
        {
            if (!$this->sendParentAccountIntroductionMail($user, $userRepository, $mailFactory))
            {
                return new JsonResponse(['error' => 'Account is aangemaakt, maar kon welkomstmail niet versturen'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $userSession->addNotification('Ouderaccount aangemaakt.');
        return new JsonResponse();
    }

    private function sendParentAccountIntroductionMail(User $user, UserRepository $userRepository, MailFactory $mailFactory): bool
    {
        $password = Util::generatePassword();
        $user->setPassword($password);
        $userRepository->save($user);

        $mailBody = $this->templateRenderer->render('Geelhoed/Contest/Page/ParentAccountIntroductionMail', [
            'fullName' => $user->getFullName(),
            'email' => $user->email,
            'password' => $password,
        ]);

        assert($user->email !== null);
        $mail = $mailFactory->createMailWithDefaults(new Address($user->email), 'Ouderaccount aangemaakt', $mailBody);
        return $mail->send();
    }

    #[RouteAttribute('deleteParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function deleteParentAccount(QueryBits $queryBits, UserRepository $repository): JsonResponse
    {
        $id = $queryBits->getInt(2);
        $user = $repository->fetchById($id);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }
        if (!$repository->userHasRight($user, Contest::RIGHT_PARENT))
        {
            return new JsonResponse(['error' => 'Gebruiker is geen ouderaccount!'], Response::HTTP_BAD_REQUEST);
        }

        $repository->delete($user);

        return new JsonResponse();
    }

    #[RouteAttribute('deleteFromParentAccount', RequestMethod::POST, UserLevel::ADMIN, isApiMethod: true, right: Contest::RIGHT_MANAGE)]
    public function deleteFromParentAccount(RequestParameters $post, Connection $db, UserRepository $userRepository): JsonResponse
    {
        $userId = $post->getInt('userId');
        $user = $userRepository->fetchById($userId);
        if ($user === null)
        {
            return new JsonResponse(['error' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $memberToRemoveId = $post->getInt('memberId');
        $controlledMembers = $this->memberRepository->fetchAllByUser($user);
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
    public function addToParentAccount(RequestParameters $post, Connection $db, UserRepository $userRepository): Response
    {
        $userId = $post->getInt('userId');
        $user = $userRepository->fetchById($userId);
        if ($user === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Gebruiker bestaat niet!'), status:  Response::HTTP_NOT_FOUND);
        }

        $memberId = $post->getInt('memberId');
        $member = $this->memberRepository->fetchById($memberId);
        if ($member === null)
        {
            return $this->pageRenderer->renderResponse(new SimplePage('Fout', 'Lid bestaat niet!'), status:  Response::HTTP_NOT_FOUND);
        }

        $db->executeQuery('INSERT INTO geelhoed_users_members(`userId`, `memberId`) VALUES(?, ?)', [$userId, $memberId]);
        return new RedirectResponse('/contest/parentAccounts');
    }

    #[RouteAttribute('cancelSubscription', RequestMethod::POST, UserLevel::LOGGED_IN, isApiMethod: true)]
    public function cancelSubscription(QueryBits $queryBits, User $currentUser, GenericRepository $repository): JsonResponse
    {
        $contestMemberId = $queryBits->getInt(2);
        $contestMember = $this->contestMemberRepository->fetchById($contestMemberId);
        if ($contestMember === null)
        {
            return new JsonResponse(['message' => 'Gebruiker bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $member = $contestMember->member;
        $manageableContestants = $this->memberRepository->fetchAllContestantsByUser($currentUser);
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

        $repository->delete($contestMember);

        return new JsonResponse(['message' => 'De inschrijving is geannuleerd!']);
    }

    #[RouteAttribute('removeAsContestant', RequestMethod::POST, UserLevel::ADMIN, right: Contest::RIGHT_MANAGE)]
    public function removeAsContestant(RequestParameters $post): Response
    {
        $memberId = $post->getInt('memberId');
        $member = $this->memberRepository->fetchById($memberId);
        if ($member === null)
        {
            return new JsonResponse(['error' => 'Lid bestaat niet!'], Response::HTTP_NOT_FOUND);
        }

        $member->isContestant = false;
        $this->memberRepository->save($member);

        return new RedirectResponse('/contest/contestantsList');
    }
}
