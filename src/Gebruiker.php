<?php
namespace Cyndaron;

class Gebruiker
{
    public static function isAdmin()
    {
        if (!isset($_SESSION['naam']) || $_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] || $_SESSION['niveau'] < 4)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function nieuweMelding(string $tekst)
    {
        $_SESSION['meldingen'][] = $tekst;
    }

    public static function geefMeldingen()
    {
        $return = @$_SESSION['meldingen'];
        $_SESSION['meldingen'] = null;
        return $return;
    }
}