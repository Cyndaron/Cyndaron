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
    protected const MONTHS = ['', 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    protected const WEEKDAYS = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];

    /**
     * Zet een maandnummer om in de naam.
     * Bijvoorbeeld: 1 -> januari.
     *
     * @param int $number Het maandnummer, waarbij 1 januari is en 12 december.
     * @return string De naam van de maand, bijvoorbeeld "januari".
     */
    public static function getMonth(int $number): string
    {
        return static::MONTHS[$number];
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
        return static::WEEKDAYS[$number % 7];
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

        return $text;
    }

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
        return $bool ? 'Ja' : 'Nee';
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

    public static function filterHm(string $hms): string
    {
        $parts = explode(':', $hms);
        return "$parts[0]:$parts[1]";
    }

    public static function parseText(string $text): string
    {
        $text = preg_replace_callback('/%slider\|(\d+)%/', static function($matches)
        {
            $album = Photoalbum::loadFromDatabase($matches[1]);
            $page = new PhotoalbumPage($album, 1);
            if ($album && $page)
            {
                return $page->drawSlider($album);
            }
            return '';
        }, $text);
        $youtube = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/$1" sandbox="allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe></div>';
        $text = preg_replace('/%youtube\|([A-Za-z0-9_\-]+)%/', $youtube, $text);

        return $text;
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
        $year = date('Y');
        $nextYear = $year + 1;
        $currentQuarter = floor((date('m') - 1) / 3) + 1;

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

    public static function filterDutchDate(string $date): string
    {
        $day = date('j');
        $month = static::getMonth(date('m', strtotime($date)));
        $year = date('Y');
        return sprintf('%s %s %s', $day, $month, $year);
    }

    public static function filterDutchDateTime(string $date): string
    {
        return sprintf('%s om %s', static::filterDutchDate($date), date('H:i', strtotime($date)));
    }

    public static function getButtonIconAndClass(string $kind): array
    {
        $btnClass = 'btn-outline-cyndaron';

        switch ($kind)
        {
            case 'new':
                $icon = 'plus';
                $btnClass = 'btn-success';
                break;
            case 'edit':
                $icon = 'pencil';
                break;
            case 'delete':
                $icon = 'trash';
                $btnClass = 'btn-danger';
                break;
            case 'lastversion':
                $icon = 'lastversion';
                break;
            case 'addtomenu':
                $icon = 'bookmark';
                break;
            default:
                $icon = $kind;
        }
        return [$icon, $btnClass];
    }

    /**
     * @param int $numPages
     * @param int $currentPage
     * @return int[]
     *
     * todo: Filter out impossible page numbers
     */
    public static function determinePages(int $numPages, int $currentPage): array
    {
        $pagesToShow = [
            1, 2, 3,
            $numPages, $numPages - 1, $numPages - 2,
            $currentPage - 2, $currentPage - 1, $currentPage, $currentPage + 1, $currentPage + 2,
        ];

        if ($currentPage === 7)
        {
            $pagesToShow[] = 4;
        }
        if ($numPages - $currentPage === 6)
        {
            $pagesToShow[] = $numPages - 3;
        }

        $pagesToShow = array_unique($pagesToShow);
        natsort($pagesToShow);
        return $pagesToShow;
    }
}