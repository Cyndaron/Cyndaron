<?php
namespace Cyndaron;

use Cyndaron\User\User;

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
        'bewerk-fotoalbum' => '\Cyndaron\BewerkFotoalbum',
        'bewerk-statischepagina' => '\Cyndaron\BewerkStatischePagina',
        'category' => \Cyndaron\Category\CategoryController::class,
        'configuratie' => '\Cyndaron\ConfiguratiePagina',
        'editor-categorie' => '\Cyndaron\EditorCategorie',
        'editor-foto' => '\Cyndaron\EditorFoto',
        'editor-fotoalbum' => '\Cyndaron\EditorFotoalbum',
        'editor-statischepagina' => '\Cyndaron\EditorStatischePagina',
        'login' => '\Cyndaron\LoginPagina',
        'logoff' => '\Cyndaron\Loguit',
        'menu-editor' => Menu\MenuEditorController::class,
        'migreer_naar_v4.php' => '\Cyndaron\MigreerNaar4_0',
        'migreer_naar_v5.php' => '\Cyndaron\MigreerNaar5_0',
        'migreer_naar_v5_3.php' => '\Cyndaron\MigreerNaar5_3',
        'migrate-v6_0' => \Cyndaron\Migrate60::class,
        'user' => \Cyndaron\User\UserController::class,
        'usermanager' => \Cyndaron\User\UserManagerPage::class,
        'tooncategorie.php' => \Cyndaron\Category\CategoryPage::class,
        'toonfotoboek.php' => '\Cyndaron\FotoalbumPagina',
        'toonsub.php' => '\Cyndaron\StatischePagina',
        'verwerkmailformulier.php' => '\Cyndaron\VerwerkMailformulierPagina',

        'pagemanager' => \Cyndaron\PageManager\PageManagerController::class,
        // Standaard-plugins
        'bestandenkast.php' => '\Cyndaron\Bestandenkast\OverzichtPagina',
        'kaartenbestellen.php' => '\Cyndaron\Kaartverkoop\KaartenBestellenPagina',
        'overzicht-bestellingen.php' => '\Cyndaron\Kaartverkoop\OverzichtBestellingenPagina',
        'kaarten-gereserveerde-plaatsen' => '\Cyndaron\Kaartverkoop\GereserveerdePlaatsen',
        'kaarten-verwerk-bestelling' => '\Cyndaron\Kaartverkoop\VerwerkBestellingPagina',
        'kaarten-ajax-endpoint' => '\Cyndaron\Kaartverkoop\AjaxEndpoint',
        'kaarten-update-bestelling' => '\Cyndaron\Kaartverkoop\BestellingUpdate',
        'mc-leden' => '\Cyndaron\Minecraft\LedenPagina',
        'mc-skinrenderer' => '\Cyndaron\Minecraft\SkinRendererHandler',
        'mc-status' => '\Cyndaron\Minecraft\StatusPagina',
        'minecraft' => \Cyndaron\Minecraft\MinecraftController::class,
        'wieiswie' => '\Cyndaron\WieIsWie\OverzichtPagina',
        'verwerkmailformulier-ldbf' => '\Cyndaron\VerwerkMailformulierPaginaLDBF',
    ];

    public function __construct()
    {
        $request = Request::geefGetVeilig('pagina') ?: '/';
        $requestVars = (explode('/', $request));
        Request::setVars($requestVars);

        if ((substr($request, 0, 1) == '.' || substr($request, 0, 1) == '/') && $request != '/')
        {
            new Error403Pagina();
            die('Deze locatie mag niet worden opgevraagd.');
        }

        $scriptSrc = "'self'";

        // De CKeditor heeft helaas nog inline scripting nodig. Op deze manier voorkomen we dat de hele site
        // daaronder moet lijden.
        if (strpos($request, 'editor-') === 0)
        {
            $scriptSrc .= " 'unsafe-inline'";
        }

        if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == "off")
        {
            $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . $redirect);
            exit();
        }

        header("Content-Security-Policy: upgrade-insecure-requests; script-src $scriptSrc; font-src 'self'; base-uri 'none'; object-src 'none'");

        $hoofdurl = new Url(DBConnection::geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)', []));
        if ($hoofdurl->isGelijkAan(new Url($request)))
        {
            header('Location: /');
        }

        // Verwijs oude URLs door
        if ($url = DBConnection::geefEen('SELECT naam FROM friendlyurls WHERE doel=?', [basename(substr($_SERVER['REQUEST_URI'], 1))]))
        {
            header('Location: ' . $url);
        }

        if (empty($_SESSION))
        {
            session_start();
        }

        //Hoofdpagina
        if ($request == '/')
        {
            $this->verwerkUrl($hoofdurl, '/');
        }
        elseif (array_key_exists($requestVars[0], $this->endpoints))
        {
            $this->routeFoundNowCheckLogin($requestVars[0]);
            $classname = $this->endpoints[$requestVars[0]];
            if (is_subclass_of($classname, Controller::class))
            {
                /** @var Controller $route */
                $route = new $classname($requestVars[0], $requestVars[1] ?? '');
                $token = Request::geefPostVeilig('csrfToken');
                $route->checkCSRFToken($token);
                $route->route();
            }
            else
            {
                new $classname();
            }
        }

        // Bekende friendly URL
        elseif ($url = new Url(DBConnection::geefEen('SELECT doel FROM friendlyurls WHERE naam=?', [$requestVars[0]])))
        {
            $this->verwerkUrl($url, $requestVars[0]);
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

    public function verwerkUrl(Url $url, string $request)
    {
        $ufUrl = $url->geefUnfriendly();
        if (strpos($ufUrl, '?') !== false)
        {
            list($bestand, $rest) = explode('?', $ufUrl, 2);
            $restarray = explode('&', $rest);
            $_GET = ['friendlyurls' => true];
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
            $this->routeFoundNowCheckLogin($request);
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

    public function routeFoundNowCheckLogin($request)
    {
        $userLevel = User::getLevel();
        if (!User::hasSufficientReadLevel() && strpos($request, 'login') !== 0)
        {
            Request::sendDoNotCache();
            if ($userLevel > 0)
            {
                new Error403Pagina();
                die('Deze pagina mag niet worden opgevraagd.');
            }
            else
            {
                User::addNotification('U moet inloggen om deze site te bekijken');
                $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                header('Location: /login');
                die();
            }
        }
    }
}
