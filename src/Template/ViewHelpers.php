<?php
namespace Cyndaron\Template;

use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Photoalbum\PhotoalbumPage;
use Cyndaron\User\User;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

use Safe\Exceptions\DatetimeException;
use function Safe\date;
use function Safe\natsort;
use function Safe\preg_replace;
use function Safe\sprintf;
use function Safe\strtotime;
use function strip_tags;
use function count;
use function implode;
use function array_slice;
use function number_format;
use function explode;
use function array_key_exists;
use function ob_start;
use function ob_get_clean;
use function preg_replace_callback;
use function array_unique;

final class ViewHelpers
{
    protected const DUTCH_MONTHS = ['', 'januari', 'februari', 'maart', 'april', 'mei', 'juni', 'juli', 'augustus', 'september', 'oktober', 'november', 'december'];
    protected const DUTCH_WEEKDAYS = ['zondag', 'maandag', 'dinsdag', 'woensdag', 'donderdag', 'vrijdag', 'zaterdag'];

    protected const BUTTON_TYPE_TO_ICON = [
        'new' => 'plus',
        'edit' => 'pencil',
        'delete' => 'trash',
        'lastversion' => 'lastversion',
        'addtomenu' => 'bookmark',
    ];

    protected const BUTTON_TYPE_TO_CLASS = [
        'new' => 'btn-success',
        'delete' => 'btn-danger',
    ];

    private const YOUTUBE = '<div class="embed-responsive embed-responsive-16by9"><iframe class="embed-responsive-item" src="https://www.youtube.com/embed/$1" sandbox="allow-scripts allow-same-origin allow-popups" allowfullscreen></iframe></div>';

    /**
     * Zet een maandnummer om in de naam.
     * Bijvoorbeeld: 1 -> januari.
     *
     * @param int $number Het maandnummer, waarbij 1 januari is en 12 december.
     * @return string De naam van de maand, bijvoorbeeld "januari".
     */
    public static function getDutchMonth(int $number): string
    {
        return self::DUTCH_MONTHS[$number];
    }

    /**
     * Zet een dagnummer om in de naam.
     * Bijvoorbeeld: 0 -> zondag.
     *
     * @param int $number Het dagnummer, waarbij 0 zondag is en 6 zaterdag.
     * @return string De naam van de dag, bijvoorbeeld "zondag".
     */
    public static function getDutchWeekday(int $number): string
    {
        return self::DUTCH_WEEKDAYS[$number % 7];
    }

    /**
     * Limit a string to the specified word count.
     *
     * @param string $text The input string
     * @param int $length The maximum word count.
     * @param string $ellipsis What to use as postfix if the string is shortened.
     * @return string The shortened string, or the input string if it was short enough.
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

    public static function formatCurrency(float $amount): string
    {
        return number_format($amount, 2, ',', '.');
    }

    public static function formatEuro(float $amount): string
    {
        return '€ ' . static::formatCurrency($amount);
    }

    public static function boolToText(?bool $bool): string
    {
        return $bool ? 'Ja' : 'Nee';
    }

    public static function filterHm(string $hms): string
    {
        $parts = explode(':', $hms);
        return "$parts[0]:$parts[1]";
    }

    public static function filterDutchDate(string $date): string
    {
        try
        {
            $timestamp = strtotime($date);
        }
        catch (DatetimeException $e)
        {
            return 'Ongeldige datum';
        }
        $day = date('j', $timestamp);
        $month = self::getDutchMonth((int)date('m', $timestamp));
        $year = date('Y', $timestamp);
        return sprintf('%s %s %s', $day, $month, $year);
    }

    public static function filterDutchDateTime(string $date): string
    {
        try
        {
            $timestamp = strtotime($date);
        }
        catch (DatetimeException $e)
        {
            return 'Ongeldige datum en tijd';
        }

        return sprintf('%s om %s', self::filterDutchDate($date), date('H:i', $timestamp));
    }

    /**
     * @param string $type
     * @return string[]
     */
    public static function getButtonIconAndClass(string $type): array
    {
        $icon = $type;
        if (array_key_exists($type, self::BUTTON_TYPE_TO_ICON))
        {
            $icon = self::BUTTON_TYPE_TO_ICON[$type];
        }

        $btnClass = 'btn-outline-cyndaron';
        if (array_key_exists($type, self::BUTTON_TYPE_TO_CLASS))
        {
            $btnClass = self::BUTTON_TYPE_TO_CLASS[$type];
        }

        return [$icon, $btnClass];
    }

    public static function spreadsheetToString(Spreadsheet $spreadsheet): string
    {
        ob_start();
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
        return ob_get_clean() ?: '';
    }

    public static function parseText(string $text): string
    {
        $text = preg_replace_callback('/%slider\|(\d+)%/', static function($matches)
        {
            $album = Photoalbum::loadFromDatabase($matches[1]);
            if ($album !== null)
            {
                $page = new PhotoalbumPage($album, 1);
                return $page->drawSlider($album);
            }
            return '';
        }, $text);

        $text = preg_replace('/%youtube\|([A-Za-z0-9_\-]+)%/', self::YOUTUBE, $text ?? '');

        $text = preg_replace_callback('/%csrfToken\|([A-Za-z0-9_\-]+)\|([A-Za-z0-9_\-]+)%/', static function($matches)
        {
            return User::getCSRFToken($matches[1], $matches[2]);
        }, $text);

        return $text ?? '';
    }

    /**
     * @param int $numPages
     * @param int $currentPage
     * @throws \Safe\Exceptions\ArrayException
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
