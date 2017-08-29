<?php
namespace Cyndaron;

class Request
{
    public static function geefPostVeilig($var)
    {
        if (empty($_POST[$var]))
            return '';

        return static::wasVariabele($_POST[$var]);
    }

    public static function geefGetVeilig($var)
    {
        if (empty($_GET[$var]))
            return '';

        return static::wasVariabele($_GET[$var]);
    }

    public static function geefReferrerVeilig()
    {
        if (empty($_SERVER['HTTP_REFERER']))
            return '';

        return static::wasVariabele($_SERVER['HTTP_REFERER']);
    }

    public static function geefPostOnveilig($var)
    {
        if (empty($_POST[$var]))
            return '';

        return $_POST[$var];
    }

    public static function postIsLeeg(): bool
    {
        if (empty($_POST))
        {
            return true;
        }

        return false;
    }

    public static function getIsLeeg(): bool
    {
        if (empty($_GET))
        {
            return true;
        }

        return false;
    }

    public static function geefPostArrayVeilig()
    {
        $ret = [];

        foreach ($_POST as $key => $value)
        {
            $key = self::wasVariabele($key);
            $ret[$key] = self::wasArray($value);
        }

        return $ret;
    }

    private static function wasArray($invoer)
    {
        if (is_array($invoer))
        {
            $tmp = [];
            foreach ($invoer as $key => $value)
            {
                $tmp[$key] = self::wasArray($value);
            }
            return $tmp;
        }
        else
        {
            return self::wasVariabele($invoer);
        }
    }

    public static function wasVariabele($var)
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

}