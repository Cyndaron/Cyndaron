<?php
require_once('functies.db.php');
require_once('functies.url.php');

if (!file_exists(__DIR__ . '/instellingen.php'))
{
    echo 'Geen instellingenbestand gevonden!';
    die();
}

$request = !empty($_GET['pagina']) ? htmlentities($_GET['pagina'], null, 'UTF-8') : '/';

if ((substr($request, 0, 1) == '.' || substr($request, 0, 1) == '/') && $request != '/')
{
    header('Location: 403.php');
    die('Deze locatie mag niet worden opgevraagd.');
}

$hoofdurl = geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)', array());
if (geefUnfriendlyUrl($hoofdurl) == geefUnfriendlyUrl($request))
{
    header('Location: /');
}

//Hoofdpagina
if ($request == '/')
{
    verwerkUrl($hoofdurl);
}
//Non-friendly URL
elseif (strpos($request, '.php'))
{
    verwerkUrl($request);
}
//Normaal bestand
elseif (@file_exists($request))
{
    include($request);
}
//Bekende URL
elseif ($url = geefEen('SELECT doel FROM friendlyurls WHERE naam=?', array($request)))
{
    verwerkUrl($url);
}
//Normaal bestand zonder .php
elseif (@file_exists($request . '.php'))
{
    include($request . '.php');
}
//Niet gevonden
else
{
    header('Location: 404.php');
}
