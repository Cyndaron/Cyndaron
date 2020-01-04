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

use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Photoalbum\PhotoalbumPage;

class Util
{
    private static $months = ["", "januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december"];
    private static $weekDays = ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"];

    /**
     * Zet een maandnummer om in de naam.
     * Bijvoorbeeld: 1 -> januari.
     *
     * @param int $number Het maandnummer, waarbij 1 januari is en 12 december.
     * @return string De naam van de maand, bijvoorbeeld "januari".
     */
    public static function getMonth(int $number): string
    {
        return static::$months[$number];
    }

    /**
     * Zet een dagnummer om in de naam.
     * Bijvoorbeeld: 0 -> zondag.
     *
     * @param int $number Het dagnummer, waarbij 0 zondag is en 6 zaterdag.
     * @return string De naam van de dag, bijvoorbeeld "zondag".
     */
    public static function getWeekday(int $number): string
    {
        return static::$weekDays[$number];
    }

    /**
     * Beperkt een string tot het opgegeven aantal woorden.
     *
     * @param string $text De string die ingekort moet worden
     * @param int $length Het maximumaantal woorden
     * @param string $ellipsis Wat er als weglatingsteken moet worden gebruikt
     * @return string De ingekorte string, of de originele string als deze korter was dan het maximum
     */
    public static function wordlimit(string $text, int $length = 50, string $ellipsis = '…'): string
    {
        $text = strip_tags($text);
        $words = explode(' ', $text);
        if (count($words) > $length)
        {
            return implode(' ', array_slice($words, 0, $length)) . $ellipsis;
        }
        else
        {
            return $text;
        }
    }

    public static function generatePassword($length = 10): string
    {
        $gencode = '';
        $letters = ['a', 'c', 'd', 'e', 'f', 'h', 'j', 'm', 'n', 'q', 'r', 't',
            'A', 'C', 'D', 'E', 'F', 'H', 'J', 'L', 'M', 'N', 'Q', 'R', 'T',
            '3', '4', '7', '8'];

        for ($c = 0; $c < $length; $c++)
        {
            $gencode .= $letters[rand(0, count($letters) - 1)];
        }

        return $gencode;
    }

    public static function generateToken(int $length): string
    {
        return bin2hex(random_bytes($length));
    }

    public static function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }

    public static function formatEuro(float $amount): string
    {
        return '€ ' . static::formatCurrency($amount);
    }

    public static function boolToText(bool $bool): string
    {
        if ($bool == true)
            return 'Ja';
        return 'Nee';
    }

    public static function mail(string $to, string $subject, string $message, string $fromAddress, string $fromName): bool
    {
        $additionalHeaders = [
            'From' => "\"$fromName\" <$fromAddress>",
            'Content-Type' => 'text/plain; charset="UTF-8"',
        ];
        // Set the envelope sender. This is often needed to make DMARC checks pass if multiple domains send mail from the same server.
        $additionalParameters = "-f$fromAddress";

        return mail($to, $subject, $message, $additionalHeaders, $additionalParameters);
    }

    public static function filterHm(string $hms): string
    {
        $parts = explode(':', $hms);
        return "$parts[0]:$parts[1]";
    }

    public static function parseText(string $text): string
    {
        return preg_replace_callback('/%slider\|([0-9]+)%/', function($matches) {
            $album = Photoalbum::loadFromDatabase($matches[1]);
            $page = new PhotoalbumPage($album, 1);
            return $page->drawSlider($album);
        }, $text);
    }

    public static function getDomain(): string
    {
        $domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);
        $domain = str_replace("/", "", $domain);

        return $domain;
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
}