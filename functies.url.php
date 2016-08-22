<?php
function verwerkUrl($url)
{
    $url = geefUnfriendlyUrl($url);
    list($bestand, $rest) = explode('?', $url, 2);
    $restarray = explode('&', $rest);
    $_GET = array('friendlyurls' => true);
    foreach ($restarray as $var)
    {
        list($key, $value) = explode('=', $var);
        $_GET[$key] = $value;
    }

    if (file_exists($bestand))
    {
        include($bestand);
    }
    else
    {
        include('404.php');
        die();
    }
}

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
    if (!empty($_POST[$var]))
        return htmlentities($_POST[$var], null, 'UTF-8');

    return '';
}

function geefGetVeilig($var)
{
    if (!empty($_GET[$var]))
        return htmlentities($_GET[$var], null, 'UTF-8');

    return '';
}

function geefReferrerVeilig()
{
    if (!empty($_SERVER['HTTP_REFERER']))
        return htmlentities($_SERVER['HTTP_REFERER'], null, 'UTF-8');

    return '';
}

function maakFriendlyUrl($naam, $doel)
{
    maakEen('INSERT INTO friendlyurls(naam,doel) VALUES (?,?)', array($naam, $doel));
}

function verwijderFriendlyUrl($naam)
{
    maakEen('DELETE FROM friendlyurls WHERE naam=?', array($naam));
}
