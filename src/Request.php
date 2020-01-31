<?php
namespace Cyndaron;

class Request
{
    protected static array $vars = [];

    public static function post($var = null)
    {
        if ($var === null)
            return static::postArray();

        if (!isset($_POST[$var]))
            return '';

        return static::cleanVariable($_POST[$var]);
    }

    public static function get($var)
    {
        if (!isset($_GET[$var]))
            return '';

        return static::cleanVariable($_GET[$var]);
    }

    public static function referrer()
    {
        if (!isset($_SERVER['HTTP_REFERER']))
            return '';

        return static::cleanVariable($_SERVER['HTTP_REFERER']);
    }

    public static function unsafePost($var)
    {
        if (!isset($_POST[$var]))
            return '';

        return $_POST[$var];
    }

    public static function postIsEmpty(): bool
    {
        if (!isset($_POST))
        {
            return true;
        }

        return false;
    }

    private static function postArray()
    {
        $ret = [];

        foreach ($_POST as $key => $value)
        {
            $key = self::cleanVariable($key);
            $ret[$key] = self::cleanArray($value);
        }

        return $ret;
    }

    private static function cleanArray($invoer)
    {
        if (is_array($invoer))
        {
            $tmp = [];
            foreach ($invoer as $key => $value)
            {
                $tmp[$key] = self::cleanArray($value);
            }
            return $tmp;
        }

        return self::cleanVariable($invoer);
    }

    public static function cleanVariable(string $var): string
    {
        // Dit filtert niet-bestaande en ongewenste UTF-8 tekens uit een string.
        // Line feeds, carriage returns en horizontale tabs zijn toegestaan.
        // Let op: de (zelden gebruikte) mb4-tekens worden er ook uitgefilterd.
        $output = preg_replace('/[\x00-\x08\x10\x0B\x0C\x0E-\x19\x7F]|(?<=^|[\x00-\x7F])[\x80-\xBF]+'.
            '|([\xC0\xC1]|[\xF0-\xFF])[\x80-\xBF]*|[\xC2-\xDF]((?![\x80-\xBF])|[\x80-\xBF]{2,})'.
            '|[\xE0-\xEF](([\x80-\xBF](?![\x80-\xBF]))|(?![\x80-\xBF]{2})|[\x80-\xBF]{3,})/',
            '', $var );
        $output = preg_replace('/\xE0[\x80-\x9F][\x80-\xBF]|\xED[\xA0-\xBF][\x80-\xBF]/S','', $output);
        $output = htmlspecialchars($output, ENT_NOQUOTES, 'UTF-8');
        return $output;
    }

    public static function sendDoNotCache(): void
    {
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
    }

    public static function setVars(array $vars): void
    {
        static::$vars = $vars;
    }

    public static function getVar(int $varNum): ?string
    {
        if ($varNum >= count(static::$vars))
        {
            return null;
        }

        return static::$vars[$varNum];
    }
}