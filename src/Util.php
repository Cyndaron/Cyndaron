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
     * @param string $string De string die ingekort moet worden
     * @param int $lengte Het maximumaantal woorden
     * @param string $ellips Wat er als weglatingsteken moet worden gebruikt
     * @return string De ingekorte string, of de originele string als deze korter was dan het maximum
     */
    public static function woordlimiet(string $string, int $lengte = 50, string $ellips = "..."): string
    {
        $string = strip_tags($string);
        $words = explode(' ', $string);
        if (count($words) > $lengte)
        {
            return implode(' ', array_slice($words, 0, $lengte)) . $ellips;
        }
        else
        {
            return $string;
        }
    }

    /**
     * @param string $waarde De waarde van de checkbox
     * @return int 1 als de checkbox was aangevinkt, 0 als dat niet zo was
     */
    public static function parseCheckboxAlsInt(string $waarde): int
    {
        if (!$waarde)
        {
            return 0;
        }
        else
        {
            return 1;
        }
    }

    /**
     * @param string $waarde De waarde van de checkbox
     * @return bool true als de checkbox was aangevinkt, false als dat niet zo was
     */
    public static function parseCheckBoxAlsBool(string $waarde): bool
    {
        if (!$waarde)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function generatePassword(): string
    {
        $gencode = '';
        $letters = ['a', 'c', 'd', 'e', 'f', 'h', 'j', 'm', 'n', 'q', 'r', 't',
            'A', 'C', 'D', 'E', 'F', 'H', 'J', 'L', 'M', 'N', 'Q', 'R', 'T',
            '3', '4', '7', '8'];

        for ($c = 0; $c < 10; $c++)
        {
            $gencode .= $letters[rand(0, count($letters))];
        }

        return $gencode;
    }
}