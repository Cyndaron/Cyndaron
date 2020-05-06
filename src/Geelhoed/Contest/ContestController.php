<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\DBConnection;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\PageManagerTabs;
use Cyndaron\Page;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\Template\Template;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class ContestController extends Controller
{
    protected array $getRoutes = [
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
        'manageOverview' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'manageOverview'],
        'subscriptionList' => ['level' => UserLevel::ADMIN, 'right' => Contest::RIGHT, 'function' => 'subscriptionList'],
    ];
    protected array $postRoutes = [
        'subscribe' => ['level' => UserLevel::LOGGED_IN, 'function' => 'subscribe'],
        'mollieWebhook' => ['level' => UserLevel::ANONYMOUS, 'function' => 'mollieWebhook'],
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

    public function view(): void
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        if ($contest)
        {
            new ContestViewPage($contest);
        }
        else
        {
            $this->send404('Wedstrijd niet gevonden!');
        }
    }

    public function subscribe(): void
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
            $this->send404('Wedstrijd niet gevonden!');
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
            'webhookUrl' => "{$baseUrl}/contest/mollieWebhook",
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
                $contestMember->save();
            }
            else
            {
                $contestMember->isPaid = false;
                $contestMember->save();
            }

            return ['status' => 'ok'];
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
        }
    }

    public function manageOverview()
    {
        $contents = PageManagerTabs::contestsTab();
        $page = new Page('Overzicht wedstrijden', $contents);
        $page->render();
    }

    public function subscriptionList()
    {
        $id = (int)Request::getVar(2);
        $contest = Contest::loadFromDatabase($id);
        new SubscriptionListPage($contest);
    }
}