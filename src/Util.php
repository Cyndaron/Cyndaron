<?php
/*
 * Copyright © 2009-2017, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
namespace Cyndaron;

class Util
{
    public static function generatePassword($length = 10): string
    {
        $gencode = '';
        $letters = ['a', 'c', 'd', 'e', 'f', 'h', 'j', 'm', 'n', 'q', 'r', 't',
            'A', 'C', 'D', 'E', 'F', 'H', 'J', 'L', 'M', 'N', 'Q', 'R', 'T',
            '3', '4', '7', '8'];

        for ($c = 0; $c < $length; $c++)
        {
            $gencode .= $letters[random_int(0, count($letters) - 1)];
        }

        return $gencode;
    }

    public static function generateToken(int $length): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function mail(string $to, string $subject, string $message, string $fromAddress = null, string $fromName = null): bool
    {
        if (empty($fromAddress))
        {
            $fromAddress = static::getNoreplyAddress();
        }
        if (empty($fromName))
        {
            $fromName = Setting::get('organisation') ?: Setting::get('siteName');
        }

        $additionalHeaders = [
            'From' => "\"$fromName\" <$fromAddress>",
            'Content-Type' => 'text/plain; charset="UTF-8"',
        ];
        // Set the envelope sender. This is often needed to make DMARC checks pass if multiple domains send mail from the same server.
        $additionalParameters = "-f$fromAddress";

        return mail($to, $subject, $message, $additionalHeaders, $additionalParameters);
    }

    public static function getDomain(): string
    {
        return str_replace(['www.', 'http://', 'https://', '/'], '', $_SERVER['HTTP_HOST']);
    }

    public static function getNoreplyAddress(): string
    {
        $domain = static::getDomain();
        return "noreply@$domain";
    }

    public static function slug(string $string): string
    {
        return strtr(strtolower($string), [
            ' ' => '-'
        ]);
    }

    public static function createDir(string $dir, int $mask = 0777): bool
    {
        $oldUmask = umask(0);
        $ret = @mkdir($dir, $mask, true);
        umask($oldUmask);

        return $ret;
    }

    public static function getStartOfNextQuarter(): \DateTimeImmutable
    {
        $year = (int)date('Y');
        $nextYear = $year + 1;
        $currentQuarter = floor(((int)date('m') - 1) / 3) + 1;

        switch ($currentQuarter)
        {
            case 1:
                $date = "$year-04-01";
                break;
            case 2:
                $date = "$year-07-01";
                break;
            case 3:
                $date = "$year-10-01";
                break;
            case 4:
            default:
                $date = "$nextYear-01-01";
                break;

        }

        return \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    }
}