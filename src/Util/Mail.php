<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use Symfony\Component\Mime\Address;
use function html_entity_decode;

final class Mail
{
    public static function getNoreplyAddressRaw(): string
    {
        $domain = Util::getDomain();
        return "noreply@$domain";
    }

    public static function getNoreplyAddress(): Address
    {
        $fromName = html_entity_decode(Setting::get(BuiltinSetting::ORGANISATION) ?: Setting::get('siteName'));
        return new Address(self::getNoreplyAddressRaw(), $fromName);
    }

    public static function createMailWithDefaults(
        Address $to,
        string $subject,
        string|null $plainTextMessage = null,
        string|null $htmlMessage = null
    ): \Cyndaron\Mail\Mail {
        $from = self::getNoreplyAddress();
        return new \Cyndaron\Mail\Mail($from, $to, $subject, $plainTextMessage, $htmlMessage);
    }
}
