<?php
namespace Cyndaron;

require_once __DIR__ . '/../functies.db.php';

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
class Router
{
    protected $endpoints = [
        // Standaard
        '403.php' => '\Cyndaron\Error403Pagina',
        '404.php' => '\Cyndaron\Error404Pagina',
        'bewerk-categorie' => '\Cyndaron\BewerkCategorie',
        'bewerk-foto' => '\Cyndaron\BewerkFoto',
        'bewerk-fotoalbum' => '\Cyndaron\BewerkFotoAlbum',
        'bewerk-statischepagina' => '\Cyndaron\BewerkStatischePagina',
        'configuratie.php' => '\Cyndaron\ConfiguratiePagina',
        'editor-categorie' => '\Cyndaron\EditorCategorie',
        'editor-foto' => '\Cyndaron\EditorFoto',
        'editor-fotoalbum' => '\Cyndaron\EditorFotoalbum',
        'editor-statischepagina' => '\Cyndaron\EditorStatischePagina',
        'login.php' => '\Cyndaron\LoginPagina',
        'logoff.php' => '\Cyndaron\Loguit',
        'overzicht.php' => '\Cyndaron\OverzichtPagina',
        'tooncategorie.php' => '\Cyndaron\CategoriePagina',
        'toonfotoboek.php' => '\Cyndaron\FotoalbumPagina',
        'toonsub.php' => '\Cyndaron\StatischePagina',
        'verwerkmailformulier.php' => '\Cyndaron\VerwerkMailFormulierPagina',
        // Standaard-plugins
        'bestandenkast.php' => '\Cyndaron\Bestandenkast\OverzichtPagina',
        'kaartenbestellen.php' => '\Cyndaron\Kaartverkoop\KaartenBestellenPagina',
        'ideeenbus.php' => '\Cyndaron\Ideeenbus\IdeeenbusPagina',
        'overzicht-bestellingen.php' => '\Cyndaron\Kaartverkoop\OverzichtBestellingenPagina',
        'kaarten-gereserveerde-plaatsen' => '\Cyndaron\Kaartverkoop\GereserveerdePlaatsen',
        'kaarten-verwerk-bestelling' => '\Cyndaron\Kaartverkoop\VerwerkBestellingPagina',
        'mc-leden' => '\Cyndaron\Minecraft\LedenPagina',
        'mc-skinrenderer' => '\Cyndaron\Minecraft\SkinRendererHandler',
        'mc-status' => '\Cyndaron\Minecraft\StatusPagina',
    ];

    public function __construct()
    {
        $request = Request::geefGetVeilig('pagina') ?: '/';

        if ((substr($request, 0, 1) == '.' || substr($request, 0, 1) == '/') && $request != '/')
        {
            new Error403Pagina();
            die('Deze locatie mag niet worden opgevraagd.');
        }

        $hoofdurl = new Url(geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)', array()));
        if ($hoofdurl->isGelijkAan(new Url($request)))
        {
            header('Location: /');
        }

        // Verwijs oude URLs door
        if (!empty(Request::geefGetVeilig('friendlyurls')) && $url = geefEen('SELECT naam FROM friendlyurls WHERE doel=?', array(basename(substr($_SERVER['REQUEST_URI'],1)))))
        {
            header('Location: '.$url);
        }

        if (empty($_SESSION))
        {
            session_start();
        }

        //Hoofdpagina
        if ($request == '/')
        {
            $this->verwerkUrl($hoofdurl);
        }
        //Non-friendly URL
//        elseif (strpos($request, '.php'))
//        {
//            $this->verwerkUrl($request);
//        }
//        //Normaal bestand
//        elseif (@file_exists($request))
//        {
//            include($request);
//        }
        elseif (array_key_exists($request, $this->endpoints))
        {
            $classname = $this->endpoints[$request];
            $handler = new $classname();
        }

        // Bekende friendly URL
        elseif ($url = new Url(geefEen('SELECT doel FROM friendlyurls WHERE naam=?', array($request))))
        {
            $this->verwerkUrl($url);
        }
        //Normaal bestand zonder .php
        elseif (@file_exists($request . '.php'))
        {
            include($request . '.php');
        }
        // Oude directe link naar een foto
        elseif ($request === 'toonfoto.php')
        {
            $boekid = Request::geefGetVeilig('boekid');
            header('Location: toonfotoboek.php?id=' . $boekid);
        }
        //Niet gevonden
        else
        {
            new Error404Pagina();
        }
    }

    public function verwerkUrl(Url $url)
    {
        $ufUrl = $url->geefUnfriendly();
        if (strpos($ufUrl, '?') !== FALSE)
        {
            list($bestand, $rest) = explode('?', $ufUrl, 2);
            $restarray = explode('&', $rest);
            $_GET = array('friendlyurls' => true);
            foreach ($restarray as $var)
            {
                list($key, $value) = explode('=', $var);
                $_GET[$key] = $value;
            }
        }
        else
        {
            $bestand = $ufUrl;
        }

        if (array_key_exists($bestand, $this->endpoints))
        {
            $classname = $this->endpoints[$bestand];
            new $classname();
        }
        elseif (file_exists($bestand))
        {
            include $bestand;
        }
        else
        {
            new Error404Pagina();
        }
    }
}