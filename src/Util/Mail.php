<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use Symfony\Component\Mime\Address;
use function html_entity_decode;

final class Mail
{
    private static function getNoreplyAddressRaw(string $domain): string
    {
        return "noreply@$domain";
    }

    public static function getNoreplyAddress(string $domain): Address
    {
        $fromName = html_entity_decode(Setting::get(BuiltinSetting::ORGANISATION) ?: Setting::get('siteName'));
        return new Address(self::getNoreplyAddressRaw($domain), $fromName);
    }

    public static function createMailWithDefaults(
        string $domain,
        Address $to,
        string $subject,
        string|null $plainTextMessage = null,
        string|null $htmlMessage = null
    ): \Cyndaron\Mail\Mail {
        $from = self::getNoreplyAddress($domain);
        return new \Cyndaron\Mail\Mail($from, $to, $subject, $plainTextMessage, $htmlMessage);
    }
}
