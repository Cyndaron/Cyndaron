<?php
declare (strict_types = 1);

namespace Cyndaron;

use Cyndaron\User\User;

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
class Router
{
    private $request = '';
    private $requestVars = [''];

    protected $endpoints = [
        // Default endpoints
        '403' => '\Cyndaron\Error403Pagina',
        '404' => '\Cyndaron\Error404Pagina',
        'category' => \Cyndaron\Category\CategoryController::class,
        'editor' => \Cyndaron\Editor\EditorController::class,
        'friendlyurl' => \Cyndaron\FriendlyUrl\FriendlyUrlController::class,
        'login' => '\Cyndaron\LoginPagina',
        'logoff' => '\Cyndaron\Loguit',
        'menu' => Menu\MenuController::class,
        'menu-editor' => Menu\MenuEditorController::class,
        'migreer_naar_v5.php' => '\Cyndaron\MigreerNaar5_0',
        'migreer_naar_v5_3.php' => '\Cyndaron\MigreerNaar5_3',
        'migrate-v6_0' => \Cyndaron\Migrate60::class,
        'pagemanager' => \Cyndaron\PageManager\PageManagerController::class,
        'photoalbum' => \Cyndaron\Photoalbum\PhotoalbumController::class,
        'sub' => \Cyndaron\StaticPage\StaticPageController::class,
        'system' => \Cyndaron\System\SystemController::class,
        'user' => \Cyndaron\User\UserController::class,
        'usermanager' => \Cyndaron\User\UserManagerPage::class,
        'verwerkmailformulier.php' => '\Cyndaron\VerwerkMailformulierPagina',

        // Official plugins
        'bestandenkast.php' => '\Cyndaron\Bestandenkast\OverzichtPagina',
        'kaartenbestellen.php' => '\Cyndaron\Kaartverkoop\KaartenBestellenPagina',
        'overzicht-bestellingen.php' => '\Cyndaron\Kaartverkoop\OverzichtBestellingenPagina',
        'kaarten-gereserveerde-plaatsen' => '\Cyndaron\Kaartverkoop\GereserveerdePlaatsen',
        'kaarten-verwerk-bestelling' => '\Cyndaron\Kaartverkoop\VerwerkBestellingPagina',
        'kaarten-ajax-endpoint' => '\Cyndaron\Kaartverkoop\AjaxEndpoint',
        'kaarten-update-bestelling' => '\Cyndaron\Kaartverkoop\BestellingUpdate',
        'minecraft' => \Cyndaron\Minecraft\MinecraftController::class,
        'wieiswie' => '\Cyndaron\WieIsWie\OverzichtPagina',
        'verwerkmailformulier-ldbf' => '\Cyndaron\VerwerkMailformulierPaginaLDBF',
    ];

    public function __construct()
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $request = Request::geefGetVeilig('page') ?: '/';
        $this->updateRequestVars($request);

        $this->blockPathTraversal($request);

        $this->sendCSPHeader();

        $this->redirectOldUrls($request);

        // Frontpage
        if ($this->requestVars[0] === '')
        {
            $frontpage = $this->getFrontpageUrl();
            $this->updateRequestVars((string)$frontpage);
        }
        // Known friendly URL
        elseif ($url = DBConnection::geefEen('SELECT doel FROM friendlyurls WHERE naam=?', [$this->requestVars[0]]))
        {
            $this->updateRequestVars($this->rewriteFriendlyUrl(new Url($url)));
        }

        if (array_key_exists($this->requestVars[0], $this->endpoints))
        {
            $this->routeEndpoint();
        }
        else
        {
            new Error404Pagina();
        }
    }

    private function routeFoundNowCheckLogin()
    {
        $userLevel = User::getLevel();
        if (!User::hasSufficientReadLevel() && $this->requestVars[0] !== 'login')
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

    private function routeEndpoint(): void
    {
        $this->routeFoundNowCheckLogin();
        $classname = $this->endpoints[$this->requestVars[0]];
        if (is_subclass_of($classname, Controller::class)) {
            /** @var Controller $route */
            $route = new $classname($this->requestVars[0], $this->requestVars[1] ?? '');
            $token = Request::geefPostVeilig('csrfToken');
            $route->checkCSRFToken($token);
            $route->route();
        } else {
            new $classname();
        }
    }

    /**
     * @param Url $url
     * @return string
     */
    private function rewriteFriendlyUrl(Url $url): string
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
        return $bestand;
    }

    /**
     * @param string $request
     */
    private function redirectOldUrls(string $request): void
    {
        $frontPage = $this->getFrontpageUrl();
        if ($frontPage->isGelijkAan(new Url($_SERVER['REQUEST_URI'])))
        {
            header('Location: /');
            die();
        }
        // Redirect if a friendly url exists for the requested unfriendly url
        if ($_SERVER['REQUEST_URI'] != '/' && $url = DBConnection::geefEen('SELECT naam FROM friendlyurls WHERE doel = ?', [$_SERVER['REQUEST_URI']]))
        {
            header('Location: /' . $url);
            die();
        }
        if ($request === 'toonsub.php')
        {
            $id = Request::geefGetVeilig('id');
            header('Location: /sub/' . $id);
            die();
        }
        if ($request === 'tooncategorie.php')
        {
            $id = Request::geefGetVeilig('id');
            header('Location: /category/' . $id);
            die();
        }
        if ($request === 'toonfotoboek.php')
        {
            $id = Request::geefGetVeilig('id');
            header('Location: /category/' . $id);
            die();
        }
        // Old link to photo in album (pre-Lightbox)
        if ($request === 'toonfoto.php')
        {
            $albumId = Request::geefGetVeilig('boekid');
            header('Location: /photoalbum/' . $albumId);
            die();
        }
    }

    /**
     * @return Url
     */
    private function getFrontpageUrl(): Url
    {
        $frontPage = new Url(DBConnection::geefEen('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)', []));
        return $frontPage;
    }

    private function sendCSPHeader(): void
    {
        $scriptSrc = "'self'";

        // Unfortunately, CKeditor still needs inline scripting. Only allow this on editor pages,
        // in order to prevent degrading the security of the rest of the system.
        if ($this->requestVars[0] == 'editor')
        {
            $scriptSrc .= " 'unsafe-inline'";
        }

        header("Content-Security-Policy: upgrade-insecure-requests; script-src $scriptSrc; font-src 'self'; base-uri 'none'; object-src 'none'");
    }

    /**
     * @param string $request
     */
    private function blockPathTraversal(string $request): void
    {
        if ((substr($request, 0, 1) == '.' || substr($request, 0, 1) == '/') && $request != '/') {
            new Error403Pagina();
            die('Deze locatie mag niet worden opgevraagd.');
        }
    }

    /**
     * @param string $request
     */
    private function updateRequestVars(string $request): void
    {
        $this->requestVars = explode('/', trim($request, '/'));
        Request::setVars($this->requestVars);
    }
}
