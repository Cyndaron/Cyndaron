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
    private $requestVars = [''];

    protected $endpoints = [
        // Default endpoints
        'category' => \Cyndaron\Category\CategoryController::class,
        'editor' => \Cyndaron\Editor\EditorController::class,
        'error' => \Cyndaron\Error\ErrorController::class,
        'friendlyurl' => \Cyndaron\FriendlyUrl\FriendlyUrlController::class,
        'menu' => Menu\MenuController::class,
        'menu-editor' => Menu\MenuEditorController::class,
        'migrate-v5_3' => \Cyndaron\Migrate53::class,
        'migrate-v6_0' => \Cyndaron\Migrate60::class,
        'pagemanager' => \Cyndaron\PageManager\PageManagerController::class,
        'photoalbum' => \Cyndaron\Photoalbum\PhotoalbumController::class,
        'sub' => \Cyndaron\StaticPage\StaticPageController::class,
        'system' => \Cyndaron\System\SystemController::class,
        'user' => \Cyndaron\User\UserController::class,
        'usermanager' => \Cyndaron\User\UserManagerPage::class,

        // Official plugins
        'concert' => \Cyndaron\Concerts\ConcertController::class,
        'concert-order' => \Cyndaron\Concerts\OrderController::class,
        'file-cabinet' => \Cyndaron\FileCabinet\FileCabinetController::class,
        'minecraft' => \Cyndaron\Minecraft\MinecraftController::class,
        'verwerkmailformulier-ldbf' => '\Cyndaron\VerwerkMailformulierPaginaLDBF',
    ];

    const OLD_URLS = [
        'tooncategorie.php' => ['url' => '/category/', 'id' => 'id'],
        'toonfoto.php' => ['url' => '/photoalbum/', 'id' => 'boekid'], // Old link to photo in album (pre-Lightbox)
        'toonfotoboek.php' => ['url' => '/photoalbum/', 'id' => 'id'],
        'toonsub.php' => ['url' => '/sub/', 'id' => 'id'],
        'verwerkmailformulier.php' => ['url' => '/mailform/process/', 'id' => 'id'],
    ];

    public function __construct()
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $request = Request::get('page') ?: '/';
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
        elseif ($url = DBConnection::doQueryAndFetchOne('SELECT doel FROM friendlyurls WHERE naam=?', [$this->requestVars[0]]))
        {
            $this->updateRequestVars($this->rewriteFriendlyUrl(new Url($url)));
        }

        if (!array_key_exists($this->requestVars[0], $this->endpoints))
        {
            $this->updateRequestVars('/error/404');
        }

        $this->routeEndpoint();
    }

    private function routeFoundNowCheckLogin()
    {
        $userLevel = User::getLevel();
        if (!User::hasSufficientReadLevel() && !($this->requestVars[0] === 'user' && $this->requestVars[1] === 'login'))
        {
            Request::sendDoNotCache();
            if ($userLevel > 0)
            {
                header('Location: /error/403');
                die('Deze pagina mag niet worden opgevraagd.');
            }
            else
            {
                User::addNotification('U moet inloggen om deze site te bekijken');
                $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
                header('Location: /user/login');
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
            $token = Request::post('csrfToken');
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
        $ufUrl = $url->getUnfriendly();
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
        if ($frontPage->equals(new Url($_SERVER['REQUEST_URI'])))
        {
            header('Location: /');
            die();
        }
        // Redirect if a friendly url exists for the requested unfriendly url
        if ($_SERVER['REQUEST_URI'] != '/' && $url = DBConnection::doQueryAndFetchOne('SELECT naam FROM friendlyurls WHERE doel = ?', [$_SERVER['REQUEST_URI']]))
        {
            header('Location: /' . $url);
            die();
        }
        if (array_key_exists($request, self::OLD_URLS))
        {
            $url = self::OLD_URLS[$request]['url'];
            $id = Request::get(self::OLD_URLS[$request]['id']);
            header("Location: ${url}${id}");
            die();
        }
    }

    /**
     * @return Url
     */
    private function getFrontpageUrl(): Url
    {
        $frontPage = new Url(DBConnection::doQueryAndFetchOne('SELECT link FROM menu WHERE volgorde=(SELECT MIN(volgorde) FROM menu)', []));
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
            header('Location: /error/403');
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
