<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Setting;
use Cyndaron\Template\ViewHelpers;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util;
use PhpOffice\PhpSpreadsheet\Shared\Date as PHPSpreadsheetDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Safe\DateTime;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function Safe\error_log;
use function Safe\sprintf;
use function Safe\strtotime;

final class ContestController extends Controller
{
    protected array $getRoutes = [
        'myContests' => ['level' => UserLevel::LOGGED_IN, 'function' => 'myContests'],
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'manageOverview' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'manageOverview'],
        'subscriptionList' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'subscriptionList'],
        'subscriptionListExcel' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'subscriptionListExcel'],
        'contestantsList' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'contestantsList'],
        'contestantsListExcel' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'contestantsListExcel'],
    ];

    protected array $postRoutes = [
        'addItem' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'addItem'],
        'subscribe' => ['level' => UserLevel::LOGGED_IN, 'function' => 'subscribe'],
    ];

    protected array $apiPostRoutes = [
        'edit' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'createOrEdit'],
        'delete' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'delete'],
        'mollieWebhook' => ['level' => UserLevel::ANONYMOUS, 'function' => 'mollieWebhook'],
        'updatePaymentStatus' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'updatePaymentStatus'],
        'removeSubscription' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT_MANAGE, 'function' => 'removeSubscription'],
    ];

    public function checkCSRFToken(string $token): bool
    {
        // Mollie webhook does not need a CSRF token.
        // It only notifies us of a status change and it's up to us to check with them what that status is.
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $this->action === 'mollieWebhook')
        {
            return true;
        }

        return parent::checkCSRFToken($token);
    }

    public function overview(): Response
    {
        $page = new OverviewPage();
        return new Response($page->render());
    }

    public function view(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            $page = new Page('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $page = new ContestViewPage($contest);
        return new Response($page->render());
    }

    public function subscribe(RequestParameters $post): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            $page = new Page('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $member = Member::loadFromLoggedInUser();
        assert($member !== null);
        assert($contest->id !== null);
        assert($member->id !== null);
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
            $page = new Page('Fout bij inschrijven', 'Kon de inschrijving niet opslaan!');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // No need to pay, so just redirect.
        if ($contest->price <= 0.00)
        {
            return new RedirectResponse("/contest/view/{$contest->id}");
        }

        try
        {
            $baseUrl = "https://{$_SERVER['HTTP_HOST']}";
            $redirectUrl = "{$baseUrl}/contest/view/{$contest->id}";
            $response = $this->doMollieTransaction([$contestMember], "Inschrijving {$contest->name}", $contest->price, $redirectUrl);
        }
        catch (\Exception $e)
        {
            User::addNotification('Je inschrijving is opgeslagen, maar de betaling is mislukt!');
            $response = new RedirectResponse("/contest/view/{$contest->id}");
        }

        return $response;
    }

    /**
     * @param ContestMember[] $contestMembers
     * @param string $description
     * @param float $price
     * @param string $redirectUrl
     * @throws \Cyndaron\Error\ImproperSubclassing
     * @throws \Mollie\Api\Exceptions\ApiException
     * @return Response
     */
    private function doMollieTransaction(array $contestMembers, string $description, float $price, string $redirectUrl): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $formattedAmount = number_format($price, 2, '.', '');
        $baseUrl = "https://{$_SERVER['HTTP_HOST']}";

        $payment = $mollie->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => $formattedAmount,
            ],
            'description' => $description,
            'redirectUrl' => $redirectUrl,
            'webhookUrl' => "{$baseUrl}/api/contest/mollieWebhook",
        ]);

        if (empty($payment->id))
        {
            $page = new Page('Fout bij inschrijven', 'Betaling niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        foreach ($contestMembers as $contestMember)
        {
            $contestMember->molliePaymentId = $payment->id;
            if (!$contestMember->save())
            {
                $page = new Page('Fout bij inschrijven', 'Kon de betalings-ID niet opslaan!');
                return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        $redirectUrl = $payment->getCheckoutUrl();
        if ($redirectUrl === null)
        {
            User::addNotification('Bedankt voor je inschrijving! Helaas lukte het doorsturen naar de betaalpagina niet.');
            return new RedirectResponse('/');
        }

        User::addNotification('Bedankt voor je inschrijving! Het kan even duren voordat de betaling geregistreerd is.');
        return new RedirectResponse($redirectUrl);
    }

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

    public function manageOverview(): Response
    {
        $contests = PageManagerTabs::contestsTab();
        $page = new Page('Overzicht wedstrijden', $contests);
        $page->addScript('/src/Geelhoed/Contest/js/ContestManager.js');
        return new Response($page->render());
    }

    public function subscriptionList(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            return new Response('Kon de wedstrijd niet vinden!', Response::HTTP_NOT_FOUND);
        }
        $page = new SubscriptionListPage($contest);
        return new Response($page->render());
    }

    public function subScriptionListExcel(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            throw new \Exception('Wedstrijd niet gevonden!');
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Naam', 'Geslacht', 'Adres', 'Postcode', 'Woonplaats', 'Geboortedatum', 'Leeftijd', 'Band', 'Gewicht', 'JBN-nummer', 'Betaald', 'Transactie-ID', 'Opmerkingen'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }

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
                $dobExcel = PHPSpreadsheetDate::PHPToExcel(date($profile->dateOfBirth));
                $sheet->setCellValue("F{$row}", $dobExcel);
                $sheet->getStyle("F{$row}")->getNumberFormat()->setFormatCode('dd-mm-yyyy');
            }
            else
            {
                $sheet->setCellValue("F{$row}", '');
            }
            $sheet->setCellValue("G{$row}", $profile->getAge(new DateTime($contest->date)));
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

        $date = date('Y-m-d', strtotime($contest->date));
        $httpHeaders = Util::spreadsheetHeadersForFilename("Deelnemers {$contest->name} ($date).xlsx");

        return new Response(ViewHelpers::spreadsheetToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }

    public function removeSubscription(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = ContestMember::loadFromDatabase($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contestMember->delete();

        return new JsonResponse();
    }

    public function delete(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            return new JsonResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contest->delete();

        return new JsonResponse();
    }

    public function createOrEdit(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        if ($id > 0)
        {
            $contest = Contest::loadFromDatabase($id);
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
        $contest->date = $post->getDate('date');
        $contest->registrationDeadline = $post->getDate('registrationDeadline');
        $contest->price = $post->getFloat('price');
        if (!$contest->save())
        {
            return new JsonResponse(['error' => 'Could not save contest!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse();
    }

    public function updatePaymentStatus(RequestParameters $post): JsonResponse
    {
        $id = $post->getInt('id');
        $contestMember = ContestMember::loadFromDatabase($id);
        if ($contestMember === null)
        {
            return new JsonResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }

        $contestMember->isPaid = $post->getBool('isPaid');
        $contestMember->save();

        return new JsonResponse();
    }

    public function contestantsList(): Response
    {
        $page = new ContestantsListPage();
        return new Response($page->render());
    }

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

        $contestants = Member::fetchAll(['isContestant = 1'], [], 'ORDER BY lastName,tussenvoegsel,firstName');
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
                $dobExcel = PHPSpreadsheetDate::PHPToExcel(date($profile->dateOfBirth));
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
        $httpHeaders = Util::spreadsheetHeadersForFilename("Wedstrijdjudoka's (uitvoer {$date}).xlsx");

        return new Response(ViewHelpers::spreadsheetToString($spreadsheet), Response::HTTP_OK, $httpHeaders);
    }

    public function myContests(): Response
    {
        $page = new MyContestsPage();
        return new Response($page->render());
    }

    public function payFullDue(): Response
    {
        $user = User::fromSession();
        assert($user !== null);

        [$due, $contestMembers] = $this->getDue($user);
        if ($due === 0.00)
        {
            return new Response('Er staan geen betalingen open.');
        }

        try
        {
            $redirectUrl = "https://{$_SERVER['HTTP_HOST']}/contest/myContests";
            $response = $this->doMollieTransaction($contestMembers, 'Inschrijving wedstrijdjudo Sportschool Geelhoed', $due, $redirectUrl);
        }
        catch (\Exception $e)
        {
            User::addNotification('Je inschrijving is opgeslagen, maar de betaling is mislukt!');
            $response = new RedirectResponse("/contest/myContests");
        }

        return $response;
    }

    private function getDue(User $user): array
    {
        $members = Member::fetchAllByUser($user);
        if (count($members) === 0)
        {
            return [0.00, []];
        }
        $memberIds = array_map(static function(Member $elem)
        {
            return $elem->id;
        }, $members);
        $contests =  Contest::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (?))'], [implode(',', $memberIds)], 'ORDER BY date DESC');
        $contestMembers = [];
        $due = 0.00;
        foreach ($contests as $contest)
        {
            foreach ($members as $member)
            {
                $contestMember = ContestMember::fetchByContestAndMember($contest, $member);
                if ($contestMember !== null)
                {
                    if (!$contestMember->isPaid)
                    {
                        $due += $contest->price;
                        $contestMembers[] = $contestMember;
                    }
                }
            }
        }

        return [$due, $contestMembers];
    }

    public function addItem(): Response
    {
        $id = $this->queryBits->getInt(2);
        if ($id < 1)
        {
            return new Response('Incorrect ID!', Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            return new Response('Wedstrijd bestaat niet!', Response::HTTP_NOT_FOUND);
        }

        $dir = Util::UPLOAD_DIR . '/contest/' . $contest->id . '/attachments';
        Util::ensureDirectoryExists($dir);

        $filename = $dir . '/' . basename($_FILES['newFile']['name']);
        if (move_uploaded_file($_FILES['newFile']['tmp_name'], $filename))
        {
            User::addNotification('Bijlage geüpload');
        }
        else
        {
            User::addNotification('Bijlage kon niet naar de uploadmap worden verplaatst.');
        }

        return new RedirectResponse('/contest/view/' . $contest->id);
    }
}
