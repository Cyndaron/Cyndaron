<?php

function geefFriendlyUrl($url)
{
    if ($friendly = geefEen('SELECT naam FROM friendlyurls WHERE doel=?', array($url)))
        return $friendly;
    else
        return $url;
}

function geefUnfriendlyUrl($url)
{
    if ($unfriendly = geefEen('SELECT doel FROM friendlyurls WHERE naam=?', array($url)))
        return $unfriendly;
    else
        return $url;
}

function isDezelfdePagina($url1, $url2)
{
    $url1 = geefUnfriendlyUrl($url1);
    $url2 = geefUnfriendlyUrl($url2);

    if ($url1 == $url2)
    {
        return true;
    }
    else
    {
        /*$hoofdurl=geefUnfriendlyUrl(geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)',array()));
        if (($url1=='/' && $url2==$hoofdurl) || ($url2=='/' && $url1==$hoofdurl))
        {
            return true;
        }*/
        return false;
    }
}

function geefPostVeilig($var)
{
    if (empty($_POST[$var]))
        return '';

    return wasVariabele($_POST[$var]);
}

function geefGetVeilig($var)
{
    if (empty($_GET[$var]))
        return '';

    return wasVariabele($_GET[$var]);
}

function geefReferrerVeilig()
{
    if (empty($_SERVER['HTTP_REFERER']))
        return '';

    return wasVariabele($_SERVER['HTTP_REFERER']);
}

function geefPostOnveilig($var)
{
    if (empty($_POST[$var]))
        return '';

    return $_POST[$var];
}

function postIsLeeg(): bool
{
    if (empty($_POST))
    {
        return true;
    }

    return false;
}

function getIsLeeg(): bool
{
    if (empty($_GET))
    {
        return true;
    }

    return false;
}

function wasVariabele($var)
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

function maakFriendlyUrl($naam, $doel)
{
    maakEen('INSERT INTO friendlyurls(naam,doel) VALUES (?,?)', array($naam, $doel));
}

function verwijderFriendlyUrl($naam)
{
    maakEen('DELETE FROM friendlyurls WHERE naam=?', array($naam));
}
