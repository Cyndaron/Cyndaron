<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Controller;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Request;
use Cyndaron\Setting;
use Cyndaron\User\User;
use Cyndaron\User\UserLevel;

class ContestController extends Controller
{
    protected array $getRoutes = [
        'overview' => ['level' => UserLevel::ANONYMOUS, 'function' => 'overview'],
        'view' => ['level' => UserLevel::ANONYMOUS, 'function' => 'view'],
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
            }

            header('Location: /contest/view/' . $contest->id);
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
        $baseUrl = "https://{$_SERVER['HTTP_HOST']}/";

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
            $contestMember->save();
            header("Location: {$payment->getCheckoutUrl()}", true, 303);
        }
    }

    public function mollieWebhook()
    {
        $apiKey = Setting::get('mollieApiKey');
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($apiKey);

        $id = $_POST['id'];
        $payment = $mollie->payments->get($id);
        $contestMember = ContestMember::fetch(['molliePaymentId' => $id]);

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
        }
    }
}