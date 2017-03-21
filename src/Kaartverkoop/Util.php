<?php
namespace Cyndaron\Kaartverkoop;

class Util
{
    const STOELEN_PER_RIJ = 300;

    public static function postcodeLigtInWalcheren($postcode)
    {
        $postcode = intval($postcode);

        if ($postcode >= 4330 && $postcode <= 4399)
            return TRUE;
        else
            return FALSE;
    }

    public static function naarEuro($bedrag)
    {
        return '&euro;&nbsp;'.number_format($bedrag, 2, ',', '.');
    }

    public static function naarEuroPlain($bedrag)
    {
        return '€ '.number_format($bedrag, 2, ',', '.');
    }

    public static function boolNaarTekst($bool)
    {
        if ($bool == true)
            return 'Ja';
        return 'Nee';
    }
}