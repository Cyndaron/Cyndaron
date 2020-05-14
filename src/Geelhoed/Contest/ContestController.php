<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Response\JSONResponse;
use Cyndaron\Setting;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;
use Cyndaron\Util;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
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
        'removeSubscription' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'removeSubscription'],
    ];

    public function checkCSRFToken(string $token): void
    {
        // Mollie webhook does not need a CSRF token.
        // It only notifies us of a status change and it's up to us to check with them what that status is.
        if (!($_SERVER['REQUEST_METHOD'] === 'POST' && $this->action === 'mollieWebhook'))
        {
            parent::checkCSRFToken($token);
        }
    }

    public function overview(): void
    {
        new OverviewPage();
    }

    public function view()
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        if ($contest)
        {
            new ContestViewPage($contest);
        }
        else
        {
            return new JSONResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function subscribe()
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        if ($contest)
        {
            $member = Member::loadFromLoggedInUser();
            $contestMember = new ContestMember();
            $contestMember->contestId = $contest->id;
            $contestMember->memberId = $member->id;
            $contestMember->graduationId = (int)filter_input(INPUT_POST, 'graduationId', FILTER_SANITIZE_NUMBER_INT);
            $contestMember->weight = (int)filter_input(INPUT_POST, 'weight', FILTER_SANITIZE_NUMBER_INT);
            $contestMember->isPaid = false;
            if ($contestMember->save())
            {
                if ($contest->price > 0.00)
                {
                    $this->doMollieTransaction($contest, $contestMember);
                }
                else
                {
                    header('Location: /contest/view/' . $contest->id);
                }
            }
            else
            {
                $this->send500('Kon de inschrijving niet opslaan!');
            }
        }
        else
        {
            return new JSONResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }
    }

    private function doMollieTransaction(Contest $contest, ContestMember $contestMember)
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

        if ($payment && $payment->id)
        {
            $contestMember->molliePaymentId = $payment->id;
            if ($contestMember->save())
            {
                User::addNotification('Bedankt voor je inschrijving! Het kan even duren voordat de betaling geregistreerd is.');
                header("Location: {$payment->getCheckoutUrl()}", true, 303);
            }
            else
            {
                $this->send500('Kon de betalings-ID niet opslaan');
            }
        }
    }

    public function mollieWebhook()
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $_POST['id'];
        $payment = $mollie->payments->get($id);
        $contestMember = ContestMember::fetch(['molliePaymentId = ?'], [$id]);

        if ($payment && $contestMember)
        {
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

            if ($saveSucceeded)
            {
                return new JSONResponse();
            }
            else
            {
                return new JSONResponse(['error' => 'Could not update payment information!'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
        else
        {
            $message = sprintf('Poging tot updaten van transactie met id %s mislukt.', $id);
            if ($payment === null)
            {
                $message .= ' $payment is null.';
            }
            if ($contestMember === null)
            {
                $message .= ' $contestMember is null.';
            }
            error_log($message);
            return new JSONResponse(['error' => 'Could not find payment!'], Response::HTTP_NOT_FOUND);
        }
    }

    public function manageOverview()
    {
        $contests = PageManagerTabs::contestsTab();
        $page = new Page('Overzicht wedstrijden', $contests);
        $page->renderAndEcho();
    }

    public function subscriptionList()
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        new SubscriptionListPage($contest);
    }

    public function subScriptionListExcel()
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = ['Naam', 'Band', 'Gewicht', 'JBN-nummer', 'Betaald'];
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
            $sheet->setCellValue("E{$row}", Util::boolToText($contestMember->isPaid));

            $row++;
        }
        for ($i = 0, $numHeaders = count($headers); $i < $numHeaders; $i++)
        {
            $column = chr(ord('A') + $i);
            $dimension = $sheet->getColumnDimension($column);
            if ($dimension)
                $dimension->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8');
        header('Content-Disposition: attachment;filename="deelnemers.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');

        exit(0);
    }

    public function removeSubscription(): JSONResponse
    {
        $id = (int)filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $contestMember = ContestMember::loadFromDatabase($id);
        if ($contestMember === null)
        {
            return new JSONResponse(['error' => 'Contest member does not exist!'], Response::HTTP_NOT_FOUND);
        }
        else
        {
            $contestMember->delete();
        }

        return new JSONResponse();
    }

    public function delete(): JSONResponse
    {
        $id = (int)filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        $contest = Contest::loadFromDatabase($id);
        if ($contest === null)
        {
            return new JSONResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
        }
        else
        {
            $contest->delete();
        }

        return new JSONResponse();
    }

    public function createOrEdit()
    {
        $id = (int)filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
        if ($id > 0)
        {
            $contest = Contest::loadFromDatabase($id);
            if ($contest === null)
            {
                return new JSONResponse(['error' => 'Contest does not exist!'], Response::HTTP_NOT_FOUND);
            }
        }
        else
        {
            $contest = new Contest();
        }

        $contest->name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
        $contest->location = filter_input(INPUT_POST, 'location', FILTER_SANITIZE_STRING);
        $contest->sportId = (int)filter_input(INPUT_POST, 'sportId', FILTER_SANITIZE_NUMBER_INT);
        $contest->date = filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING);
        $contest->registrationDeadline = filter_input(INPUT_POST, 'registrationDeadline', FILTER_SANITIZE_STRING);
        $contest->price = (float)filter_input(INPUT_POST, 'price', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (!$contest->save())
        {
            return new JSONResponse(['error' => 'Could not save contest!'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JSONResponse();
    }
}