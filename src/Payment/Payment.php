<?php
declare(strict_types=1);

namespace Cyndaron\Payment;

use Cyndaron\Util\Setting;
use Mollie\Api\Resources\Payment as MolliePayment;
use function number_format;

final class Payment
{
    private string $description;
    private float $amount;
    private string $currency;
    private string $redirectUrl;
    private string $webhookUrl;
    private string $apiKey;

    public function __construct(
        string $description,
        float $amount,
        string $currency,
        string $redirectUrl,
        string $webhookUrl,
        string $apiKey = ''
    ) {
        $this->description = $description;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->redirectUrl = $redirectUrl;
        $this->webhookUrl = $webhookUrl;
        if ($apiKey === '')
        {
            $apiKey = Setting::get('mollieApiKey');
        }
        $this->apiKey = $apiKey;
    }

    public function sendToMollie(): MolliePayment
    {
        $mollie = new \Mollie\Api\MollieApiClient();
        $mollie->setApiKey($this->apiKey);

        $formattedAmount = number_format($this->amount, 2, '.', '');

        $payment = $mollie->payments->create([
            'amount' => [
                'currency' => $this->currency,
                'value' => $formattedAmount,
            ],
            'description' => $this->description,
            'redirectUrl' => $this->redirectUrl,
            'webhookUrl' => $this->webhookUrl,
        ]);

        return $payment;
    }
}
