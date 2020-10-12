<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\DBConnection;
use Cyndaron\Editor\EditorController;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\UserMenu;
use Cyndaron\Page;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Setting;
use Cyndaron\Url;
use Cyndaron\User\User;
use Cyndaron\Util;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

use function Safe\error_log;
use function Safe\substr;

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
final class Router
{
    private array $requestVars = [''];
    private bool $isApiCall = false;

    protected array $endpoints = [
        // Default endpoints
        'editor' => \Cyndaron\Editor\EditorController::class,
        'error' => \Cyndaron\Error\ErrorController::class,
        'menu' => \Cyndaron\Menu\MenuController::class,
        'menu-editor' => \Cyndaron\Menu\MenuEditorController::class,
        'migrate' => \Cyndaron\MigrateController::class,
        'pagemanager' => \Cyndaron\PageManager\PageManagerController::class,
        'system' => \Cyndaron\System\SystemController::class,
        'user' => \Cyndaron\User\UserController::class,
    ];

    public const OLD_URLS = [
        'tooncategorie.php' => ['url' => '/category/', 'id' => 'id'],
        'toonfoto.php' => ['url' => '/photoalbum/', 'id' => 'boekid'], // Old link to photo in album (pre-Lightbox)
        'toonfotoboek.php' => ['url' => '/photoalbum/', 'id' => 'id'],
        'toonsub.php' => ['url' => '/sub/', 'id' => 'id'],
    ];

    public const HEADERS_DO_NOT_CACHE = [
        'cache-control' => 'no-cache, no-store, must-revalidate',
        'pragma' => 'no-cache',
        'expires' => 0,
    ];

    public function route(): void
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $request = (new RequestParameters($_GET))->getUrl('page') ?: '/';
        $this->updateRequestVars($request);
        $cspHeader = $this->getCSPHeader();

        $redirect = $this->blockPathTraversal($request);
        if ($redirect !== null)
        {
            $redirect->headers->set('Content-Security-Policy', $cspHeader);
            $redirect->send();
            return;
        }

        $redirect = $this->redirectOldUrls($request);
        if ($redirect !== null)
        {
            $redirect->headers->set('Content-Security-Policy', $cspHeader);
            $redirect->send();
            return;
        }

        $this->loadModules();
        $this->rewriteFriendlyUrls($request);

        if (!array_key_exists($this->requestVars[0], $this->endpoints))
        {
            $this->updateRequestVars('/error/404');
        }

        $response = $this->routeEndpoint();
        $response->headers->set('Content-Security-Policy', $cspHeader);
        $response->send();
    }

    private function routeFoundNowCheckLogin(): ?RedirectResponse
    {
        $userLevel = User::getLevel();
        $isLoggingIn = $this->requestVars[0] === 'user' && $this->requestVars[1] === 'login';
        if (!$isLoggingIn && !User::hasSufficientReadLevel())
        {
            if ($userLevel > 0)
            {
                return new RedirectResponse('/error/403', Response::HTTP_FOUND, self::HEADERS_DO_NOT_CACHE);
            }

            User::addNotification('U moet inloggen om deze site te bekijken');
            $_SESSION['redirect'] = $_SERVER['REQUEST_URI'];
            return new RedirectResponse('/user/login', Response::HTTP_FOUND, self::HEADERS_DO_NOT_CACHE);
        }

        return null;
    }

    private function routeEndpoint(): Response
    {
        $ret = $this->routeFoundNowCheckLogin();

        if ($ret === null)
        {
            $classname = $this->endpoints[$this->requestVars[0]];
            /** @var Controller $route */
            $route = new $classname($this->requestVars[0], $this->requestVars[1] ?? '', $this->isApiCall);
            $route->setQueryBits(new QueryBits($this->requestVars));
            $ret = $this->getResponse($route);
        }

        return $ret;
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
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->__toString());
            if ($this->isApiCall)
            {
                return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $page = new Page('Fout', 'Er ging iets mis bij het laden van deze pagina!');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
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
     * @return RedirectResponse|null
     */
    private function redirectOldUrls(string $request): ?RedirectResponse
    {
        $frontPage = $this->getFrontpageUrl();
        if ($frontPage->equals(new Url($_SERVER['REQUEST_URI'])))
        {
            return new RedirectResponse('/', Response::HTTP_MOVED_PERMANENTLY);
        }
        // Redirect if a friendly url exists for the requested unfriendly url
        if ($_SERVER['REQUEST_URI'] !== '/' && $url = DBConnection::doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target = ?', [$_SERVER['REQUEST_URI']]))
        {
            return new RedirectResponse("/$url", Response::HTTP_MOVED_PERMANENTLY);
        }
        if (array_key_exists($request, self::OLD_URLS))
        {
            $url = self::OLD_URLS[$request]['url'];
            /** @phpstan-ignore-next-line (false positive) */
            $id = (int)$_GET(self::OLD_URLS[$request]['id']);
            return new RedirectResponse("${url}${id}", Response::HTTP_MOVED_PERMANENTLY);
        }
        if ($request === 'verwerkmailformulier.php')
        {
            $id = (int)$_GET['id'];
            $this->updateRequestVars("/mailform/process/$id");
        }

        return null;
    }

    /**
     * @return Url
     */
    private function getFrontpageUrl(): Url
    {
        return new Url(Setting::get('frontPage') ?: '');
    }

    private function getCSPHeader(): string
    {

        // Unfortunately, CKeditor still needs inline scripting. Only allow this on editor pages,
        // in order to prevent degrading the security of the rest of the system.
        if ($this->requestVars[0] === 'editor')
        {
            $scriptSrc = "'self' 'unsafe-inline'";
        }
        else
        {
            $nonce = self::getScriptNonce();
            $scriptSrc = "'self' 'nonce-{$nonce}' 'strict-dynamic'";
        }

        return "upgrade-insecure-requests; script-src $scriptSrc; font-src 'self'; base-uri 'none'; object-src 'none'";
    }

    /**
     * @param string $request
     * @return RedirectResponse|null
     */
    private function blockPathTraversal(string $request): ?RedirectResponse
    {
        if ($request !== '/' && (substr($request, 0, 1) === '.' || substr($request, 0, 1) === '/'))
        {
            return new RedirectResponse('/error/403');
        }

        return null;
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
            \Cyndaron\RichLink\Module::class,
        ];

        if (defined('MODULES'))
        {
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
                        PageManagerPage::addPageType([
                            $dataTypeName => [
                                'name' => $definition->plural,
                                'tabDraw' => $definition->pageManagerTab,
                                'js' => $definition->pageManagerJS ?? null,
                            ]
                        ]);
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

            if ($module instanceof UserMenu)
            {
                User::$userMenu = array_merge(User::$userMenu, $module->getUserMenuItems());
            }
        }
    }

    public static function referrer(): string
    {
        return filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL) ?: '';
    }

    /**
     * @param string $request
     */
    private function rewriteFriendlyUrls(string $request): void
    {
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
    }

    public static function getScriptNonce(): string
    {
        static $nonce;
        if (empty($nonce))
        {
            $nonce = Util::generateToken(16);
        }

        return $nonce;
    }
}
