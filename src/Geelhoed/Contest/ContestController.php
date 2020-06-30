<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Page;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Setting;
use Cyndaron\Template\ViewHelpers;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class ContestController extends Controller
{
    protected array $getRoutes = [
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'manageOverview' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'manageOverview'],
        'subscriptionList' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'subscriptionList'],
        'subscriptionListExcel' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'subscriptionListExcel'],
    ];

    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::LOGGED_IN, 'function' => 'subscribe'],
    ];

    protected array $apiPostRoutes = [
        'edit' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'createOrEdit'],
        'delete' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'delete'],
        'mollieWebhook' => ['level' => UserLevel::ANONYMOUS, 'function' => 'mollieWebhook'],
        'updatePaymentStatus' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'updatePaymentStatus'],
        'removeSubscription' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'removeSubscription'],
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
        if (!$contest)
        {
            $page = new Page('Onbekende wedstrijd', 'Kon de wedstrijd niet vinden');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $member = Member::loadFromLoggedInUser();
        $contestMember = new ContestMember();
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $contestMember->contestId = $contest->id;
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        /** @noinspection NullPointerExceptionInspection */
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
            $response = $this->doMollieTransaction($contest, $contestMember);
        }
        catch (\Exception $e)
        {
            User::addNotification('Je inschrijving is opgeslagen, maar de betaling is mislukt!');
            $response = new RedirectResponse("/contest/view/{$contest->id}");
        }

        return $response;
    }

    private function doMollieTransaction(Contest $contest, ContestMember $contestMember): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $formattedAmount = number_format($contest->price, 2, '.', '');
        $baseUrl = "https://{$_SERVER['HTTP_HOST']}";

        $payment = $mollie->payments->create([
            'amount' => [
                'currency' => 'EUR',
                'value' => $formattedAmount,
            ],
            'description' => "Inschrijving {$contest->name}",
            'redirectUrl' => "{$baseUrl}/contest/view/{$contest->id}",
            'webhookUrl' => "{$baseUrl}/api/contest/mollieWebhook",
        ]);

        if (!$payment->id)
        {
            $page = new Page('Fout bij inschrijven', 'Betaling niet gevonden!');
            return new Response($page->render(), Response::HTTP_NOT_FOUND);
        }

        $contestMember->molliePaymentId = $payment->id;
        if (!$contestMember->save())
        {
            $page = new Page('Fout bij inschrijven', 'Kon de betalings-ID niet opslaan!');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        User::addNotification('Bedankt voor je inschrijving! Het kan even duren voordat de betaling geregistreerd is.');
        return new RedirectResponse($payment->getCheckoutUrl());
    }

    public function mollieWebhook(RequestParameters $post): Response
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $post->getUnfilteredString('id');
        $payment = $mollie->payments->get($id);
        $contestMember = ContestMember::fetch(['molliePaymentId = ?'], [$id]);

        if ($contestMember === null)
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            $message .= ' $contestMember is null.';

            /** @noinspection ForgottenDebugOutputInspection */
            error_log($message);
            return new JsonResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }

        if ($payment->isPaid() && !$payment->hasRefunds() && !$payment->hasChargebacks())
        {
            $contestMember->isPaid = true;
            $saveSucceeded = $contestMember->save();
        }
        else
        {
            $contestMember->isPaid = false;
            $saveSucceeded = $contestMember->save();
        }

        if (!$saveSucceeded)
        {
            return new JsonResponse(['error' => 'Could not update payment information!'], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            return new JsonResponse(['error' => 'Incorrect ID!'], Response::HTTP_BAD_REQUEST);
        }
        $contest = Contest::loadFromDatabase($id);
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

        $headers = ['Naam', 'Band', 'Gewicht', 'JBN-nummer', 'Betaald', 'Opmerkingen'];
        foreach ($headers as $key => $value)
        {
            $column = chr(ord('A') + $key);
            $sheet->setCellValue("{$column}1", $value);
        }

        $row = 2;
        foreach ($contest->getContestMembers() as $contestMember)
        {
            $member = $contestMember->getMember();

            $sheet->setCellValue("A{$row}", $member->getProfile()->getFullName());
            $sheet->setCellValue("B{$row}", $contestMember->getGraduation()->name);
            $sheet->setCellValue("C{$row}", $contestMember->weight);
            $sheet->setCellValue("D{$row}", $member->jbnNumber);
            $sheet->setCellValue("E{$row}", ViewHelpers::boolToText($contestMember->isPaid));
            $sheet->setCellValue("F{$row}", $contestMember->comments);

            $row++;
        }
        for ($i = 0, $numHeaders = count($headers); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            $dimension->setAutoSize(true);
        }

        $date = date('Y-m-d', strtotime($contest->date));
        $filename = str_replace('"', "'", "Deelnemers {$contest->name} ($date).xlsx");

        $httpHeaders = [
            'content-type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8',
            'content-disposition' => 'attachment;filename="' . $filename . '"',
            'cache-control' => 'max-age=0'
        ];

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
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
        $contest->date = $post->getDate('date');
        /** @noinspection PhpFieldAssignmentTypeMismatchInspection */
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
}
