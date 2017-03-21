<?php
require_once('functies.db.php');
require_once('functies.url.php');

$endpoints = [
    'kaartenbestellen.php' => '\Cyndaron\Kaartverkoop\KaartenBestellenPagina',
    'overzicht-bestellingen.php' => '\Cyndaron\Kaartverkoop\OverzichtBestellingenPagina',
    'kaarten-gereserveerde-plaatsen' => '\Cyndaron\Kaartverkoop\GereserveerdePlaatsen',
    'mc-leden' => '\Cyndaron\Minecraft\LedenPagina',
    'mc-status' => '\Cyndaron\Minecraft\StatusPagina',
];


if (!file_exists(__DIR__ . '/instellingen.php'))
{
    echo 'Geen instellingenbestand gevonden!';
    die();
}

$request = geefGetVeilig('pagina') ?: '/';

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

/////
//
///
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'Cyndaron\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/src/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});



// Nog even niet...
//// Verwijs oude URLs door
//if (!empty(geefGetVeilig('friendlyurls')) && $url = geefEen('SELECT naam FROM friendlyurls WHERE doel=?', array(basename(substr($_SERVER['REQUEST_URI'],1)))))
//{
//    header('Location: '.$url);
//}
//
//if (empty($_SESSION))
//{
//    session_start();
//}

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
elseif (array_key_exists($request, $endpoints))
{
    $classname = $endpoints[$request];
    $handler = new $classname();
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
