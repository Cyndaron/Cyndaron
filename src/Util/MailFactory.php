<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use Cyndaron\Request\UrlInfo;
use Symfony\Component\Mime\Address;
use function html_entity_decode;

final class MailFactory
{
    public function __construct(
        private readonly SettingsRepository $settings,
        private readonly UrlInfo $urlInfo,
    ) {

    }

    public function getNoreplyAddress(): Address
    {
        $domain = $this->urlInfo->domain;
        $fromName = html_entity_decode($this->settings->get(BuiltinSetting::ORGANISATION) ?: $this->settings->get('siteName'));
        return new Address("noreply@$domain", $fromName);
    }

    public function createMailWithDefaults(
        Address $to,
        string $subject,
        string|null $plainTextMessage = null,
        string|null $htmlMessage = null
    ): \Cyndaron\Mail\Mail {
        $from = $this->getNoreplyAddress();
        return new \Cyndaron\Mail\Mail($from, $to, $subject, $plainTextMessage, $htmlMessage);
    }
}
