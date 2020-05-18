<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare (strict_types = 1);

namespace Cyndaron;

use Cyndaron\Editor\EditorController;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
class Router
{
    private array $requestVars = [''];
    private bool $isApiCall = false;

    protected array $endpoints = [
        // Default endpoints
        'editor' => Editor\EditorController::class,
        'error' => Error\ErrorController::class,
        'menu' => Menu\MenuController::class,
        'menu-editor' => Menu\MenuEditorController::class,
        'migrate' => MigrateController::class,
        'pagemanager' => PageManager\PageManagerController::class,
        'system' => System\SystemController::class,
        'user' => \Cyndaron\User\UserController::class,
    ];

    public const OLD_URLS = [
        'tooncategorie.php' => ['url' => '/category/', 'id' => 'id'],
        'toonfoto.php' => ['url' => '/photoalbum/', 'id' => 'boekid'], // Old link to photo in album (pre-Lightbox)
        'toonfotoboek.php' => ['url' => '/photoalbum/', 'id' => 'id'],
        'toonsub.php' => ['url' => '/sub/', 'id' => 'id'],
    ];

    public function __construct()
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $request = (new RequestParameters($_GET))->getUrl('page') ?: '/';
        $this->updateRequestVars($request);

        $this->blockPathTraversal($request);

        $this->sendCSPHeader();

        $this->redirectOldUrls($request);

        $this->loadModules();

        // Frontpage
        if ($this->requestVars[0] === '')
        {
            $frontpage = $this->getFrontpageUrl();
            $this->updateRequestVars((string)$frontpage);
        }
        // Known friendly URL
        elseif ($url = DBConnection::doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [$request]))
        {
            $this->updateRequestVars($this->rewriteFriendlyUrl(new Url($url)));
        }

        if (!array_key_exists($this->requestVars[0], $this->endpoints))
        {
            $this->updateRequestVars('/error/404');
        }

        $this->routeEndpoint();
    }

    private function routeFoundNowCheckLogin(): void
    {
        $userLevel = User::getLevel();
        $isLoggingIn = $this->requestVars[0] === 'user' && $this->requestVars[1] === 'login';
        if (!$isLoggingIn && !User::hasSufficientReadLevel())
        {
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            if ($userLevel > 0)
            {
                header('Location: /error/403');
                die('Deze pagina mag niet worden opgevraagd.');
            }

            User::addNotification('U moet inloggen om deze site te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            header('Location: /user/login');
            die();
        }
    }

    private function routeEndpoint(): void
    {
        $this->routeFoundNowCheckLogin();
        $classname = $this->endpoints[$this->requestVars[0]];
        /** @var Controller $route */
        $route = new $classname($this->requestVars[0], $this->requestVars[1] ?? '', $this->isApiCall);
        $route->setQueryBits(new QueryBits($this->requestVars));

        $ret = $this->getResponse($route);
        $ret->send();
    }

    private function getResponse(Controller $route): Response
    {
        $post = new RequestParameters($_POST);

        $token = $post->getAlphaNum('csrfToken');
        $tokenCorrect = $route->checkCSRFToken($token);
        if (!$tokenCorrect)
        {
            if ($this->isApiCall)
            {
                return new JsonResponse(['error' => 'CSRF token incorrect!'], Response::HTTP_FORBIDDEN);
            }

            $page = new Page('Controle CSRF-token gefaald!', 'Uw CSRF-token is niet correct.');
            return new Response($page->render(), Response::HTTP_FORBIDDEN);
        }

        try
        {
            return $route->route($post);
        }
        catch (\Exception $e)
        {
            error_log($e->getMessage());
            if ($this->isApiCall)
            {
                return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            return new Response(null, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Url $url
     * @return string
     */
    private function rewriteFriendlyUrl(Url $url): string
    {
        $ufUrl = $url->getUnfriendly();
        $qmPos = strpos($ufUrl, '?');
        if ($qmPos !== false)
        {
            $file = substr($ufUrl, 0, $qmPos);
        }
        else
        {
            $file = $ufUrl;
        }
        return $file;
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
        if ($_SERVER['REQUEST_URI'] !== '/' && $url = DBConnection::doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target = ?', [$_SERVER['REQUEST_URI']]))
        {
            header('Location: /' . $url);
            die();
        }
        if (array_key_exists($request, self::OLD_URLS))
        {
            $url = self::OLD_URLS[$request]['url'];
            $id = (int)$_GET(self::OLD_URLS[$request]['id']);
            header("Location: ${url}${id}");
            die();
        }
        if ($request === 'verwerkmailformulier.php')
        {
            $id = (int)$_GET['id'];
            $this->updateRequestVars("/mailform/process/$id");
        }
    }

    /**
     * @return Url
     */
    private function getFrontpageUrl(): Url
    {
        return new Url(Setting::get('frontPage') ?: '');
    }

    private function sendCSPHeader(): void
    {
        $scriptSrc = "'self'";

        // Unfortunately, CKeditor still needs inline scripting. Only allow this on editor pages,
        // in order to prevent degrading the security of the rest of the system.
        if ($this->requestVars[0] === 'editor')
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
        if ($request !== '/' && (substr($request, 0, 1) === '.' || substr($request, 0, 1) === '/'))
        {
            header('Location: /error/403');
            die('Deze locatie mag niet worden opgevraagd.');
        }
    }

    /**
     * @param string $request
     */
    private function updateRequestVars(string $request): void
    {
        $vars = explode('/', trim($request, '/'));
        if ($vars[0] === 'api')
        {
            array_shift($vars);
            $this->isApiCall = true;
        }
        $this->requestVars = $vars;
    }

    private function loadModules(): void
    {
        $modules = [
            \Cyndaron\StaticPage\Module::class,
            \Cyndaron\Category\Module::class,
            \Cyndaron\Photoalbum\Module::class,
            \Cyndaron\FriendlyUrl\Module::class,
            \Cyndaron\Mailform\Module::class,
        ];

        if (defined('MODULES')) {
            $modules = array_merge($modules, MODULES);
        }

        foreach ($modules as $moduleClass)
        {
            $module = new $moduleClass();

            if ($module instanceof Routes)
            {
                $this->endpoints = array_merge($this->endpoints, $module->routes());
            }
            if ($module instanceof Datatypes)
            {
                foreach ($module->dataTypes() as $dataTypeName => $definition)
                {
                    if (isset($definition->editorPage))
                    {
                        EditorController::addEditorPage([$dataTypeName => $definition->editorPage]);
                    }
                    if (isset($definition->editorSavePage))
                    {
                        EditorController::addEditorSavePage([$dataTypeName => $definition->editorSavePage]);
                    }
                    if (isset($definition->pageManagerTab))
                    {
                        PageManagerPage::addPageType([$dataTypeName => ['name' => $definition->plural, 'tabDraw' => $definition->pageManagerTab]]);
                    }
                    if ($module instanceof UrlProvider)
                    {
                        Url::addUrlProvider($dataTypeName, $moduleClass);
                    }
                    if ($module instanceof Linkable)
                    {
                        EditorController::addInternalLinkType($moduleClass);
                    }
                }
            }
        }
    }

    public static function referrer()
    {
        return filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL);
    }
}
