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
    private static $maanden = ["", "januari", "februari", "maart", "april", "mei", "juni", "juli", "augustus", "september", "oktober", "november", "december"];
    private static $weekdagen = ["zondag", "maandag", "dinsdag", "woensdag", "donderdag", "vrijdag", "zaterdag"];

    /**
     * Zet een maandnummer om in de naam.
     * Bijvoorbeeld: 1 -> januari.
     *
     * @param int $maandnummer Het maandnummer, waarbij 1 januari is en 12 december.
     * @return string De naam van de maand, bijvoorbeeld "januari".
     */
    public static function geefMaand(int $maandnummer): string
    {
        return static::$maanden[$maandnummer];
    }

    /**
     * Zet een dagnummer om in de naam.
     * Bijvoorbeeld: 0 -> zondag.
     *
     * @param int $dagnummer Het dagnummer, waarbij 0 zondag is en 6 zaterdag.
     * @return string De naam van de dag, bijvoorbeeld "zondag".
     */
    public static function geefDagVanDeWeek(int $dagnummer): string
    {
        return static::$weekdagen[$dagnummer];
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
        return '&euro;&nbsp;' . static::formatCurrency($amount);
    }

    public static function formatEuroPlainText(float $amount): string
    {
        return '€ ' . static::formatCurrency($amount);
    }

    public static function boolToText(bool $bool): string
    {
        if ($bool == true)
            return 'Ja';
        return 'Nee';
    }
}