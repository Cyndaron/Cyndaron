<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Connection;
use Cyndaron\Editor\EditorController;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\Page\Module\WithPageProcessors;
use Cyndaron\Page\Page;
use Cyndaron\Page\SimplePage;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\PageManager\PageManagerTab;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\RequestParameters;
use Cyndaron\Url;
use Cyndaron\User\Module\UserMenuProvider;
use Cyndaron\User\User;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateFinder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use function array_key_exists;
use function array_merge;
use function array_shift;
use function defined;
use function explode;
use function filter_input;
use function Safe\error_log;
use function session_start;
use function strpos;
use function substr;
use function trim;

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
final class Router implements HttpKernelInterface
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

        'tooncategorie.php' => \Cyndaron\Routing\OldUrlsController::class,
        'toonphoto.php' => \Cyndaron\Routing\OldUrlsController::class,
        'toonphotoalbum.php' => \Cyndaron\Routing\OldUrlsController::class,
        'toonsub.php' => \Cyndaron\Routing\OldUrlsController::class,
        'verwerkmailformulier.php' => \Cyndaron\Routing\OldUrlsController::class,
    ];

    public const HEADERS_DO_NOT_CACHE = [
        'cache-control' => 'no-cache, no-store, must-revalidate',
        'pragma' => 'no-cache',
        'expires' => 0,
    ];

    private function getEndpointOrRedirect(string $request): Response
    {
        $redirect = $this->blockPathTraversal($request);
        if ($redirect !== null)
        {
            return $redirect;
        }

        $redirect = $this->redirectOldUrls($request);
        if ($redirect !== null)
        {
            return $redirect;
        }

        $this->loadModules(User::fromSession());
        $this->rewriteFriendlyUrls($request);

        if (!array_key_exists($this->requestVars[0], $this->endpoints))
        {
            $this->updateRequestVars('/error/404');
        }

        return $this->routeEndpoint();
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
            $ret = $this->getResponse($route);
        }

        return $ret;
    }

    private function getResponse(Controller $route): Response
    {
        $request = Request::createFromGlobals();
        $post = new RequestParameters($request->request->all());

        $token = $post->getAlphaNum('csrfToken');
        $tokenCorrect = $route->checkCSRFToken($token);
        if (!$tokenCorrect)
        {
            if ($this->isApiCall)
            {
                return new JsonResponse(['error' => 'CSRF token incorrect!'], Response::HTTP_FORBIDDEN);
            }

            $page = new SimplePage('Controle CSRF-token gefaald!', 'Uw CSRF-token is niet correct.');
            return new Response($page->render(), Response::HTTP_FORBIDDEN);
        }

        try
        {
            $dic = new DependencyInjectionContainer();
            $dic->add($request);
            $dic->add($post);
            $dic->add(new QueryBits($this->requestVars));
            $pdo = DBConnection::getPDO();
            $dic->add($pdo);
            $dic->add($pdo, \PDO::class);
            $user = User::fromSession();
            if ($user !== null)
            {
                $dic->add($user);
            }

            return $route->route($dic);
        }
        catch (\Exception $e)
        {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->__toString());
            if ($this->isApiCall)
            {
                return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $page = new SimplePage('Fout', 'Er ging iets mis bij het laden van deze pagina!');
            return new Response($page->render(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param Url $url
     * @throws \Safe\Exceptions\StringsException
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
        if ($_SERVER['REQUEST_URI'] !== '/' && $url = DBConnection::getPDO()->doQueryAndFetchOne('SELECT name FROM friendlyurls WHERE target = ?', [$_SERVER['REQUEST_URI']]))
        {
            return new RedirectResponse("/$url", Response::HTTP_MOVED_PERMANENTLY);
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
        if ($this->requestVars[0] === 'editor' || ($this->requestVars[0] === 'newsletter' && $this->requestVars[1] === 'compose'))
        {
            $scriptSrc = "'self' 'unsafe-inline'";
        }
        else
        {
            $nonce = self::getScriptNonce();
            $scriptSrc = "'self' 'nonce-{$nonce}' 'strict-dynamic'";
        }

        return "upgrade-insecure-requests; frame-ancestors 'self'; default-src 'none'; base-uri 'none'; child-src 'none'; connect-src 'self'; font-src 'self'; frame-src 'self' youtube.com *.youtube.com youtu.be; img-src 'self' https: data:;  manifest-src 'none'; media-src 'self' data: https:; object-src 'none'; prefetch-src 'self'; script-src $scriptSrc; style-src 'self' 'unsafe-inline'";
    }

    /**
     * @param string $request
     * @throws \Safe\Exceptions\StringsException
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

    private function loadModules(?User $currentUser): void
    {
        $modules = [
            \Cyndaron\User\Module::class,
            \Cyndaron\View\Module::class,
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
                foreach ($module->routes() as $path => $controller)
                {
                    $this->addRoute($path, $controller);
                }
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
                        PageManagerPage::addTab(new PageManagerTab($dataTypeName, $definition->plural, $definition->pageManagerTab, $definition->pageManagerJS ?? null));
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

            if ($module instanceof UserMenuProvider)
            {
                User::$userMenu = array_merge(User::$userMenu, $module->getUserMenuItems($currentUser));
            }
            if ($module instanceof Templated)
            {
                TemplateFinder::addTemplateRoot($module->getTemplateRoot());
            }
            if ($module instanceof WithPageProcessors)
            {
                foreach ($module->getPageprocessors() as $processor)
                {
                    Page::addPreprocessor(new $processor());
                }
            }
            if ($module instanceof WithTextPostProcessors)
            {
                foreach ($module->getTextPostProcessors() as $processor)
                {
                    TextRenderer::addTextPostProcessor(new $processor());
                }
            }
        }
    }

    public static function referrer(): string
    {
        return filter_input(INPUT_SERVER, 'HTTP_REFERER', FILTER_SANITIZE_URL) ?: '';
    }

    /**
     * @param string $request
     * @throws \Safe\Exceptions\StringsException
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
        elseif ($url = DBConnection::getPDO()->doQueryAndFetchOne('SELECT target FROM friendlyurls WHERE name=?', [$request]))
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

    public function handle(Request $request, int $type = self::MASTER_REQUEST, bool $catch = true): Response
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $requestStr = (new RequestParameters($request->query->all()))->getUrl('page') ?: '/';
        $this->updateRequestVars($requestStr);
        $cspHeader = $this->getCSPHeader();
        $response = $this->getEndpointOrRedirect($requestStr);
        $response->headers->set('Content-Security-Policy', $cspHeader);

        return $response;
    }

    public function addRoute(string $path, string $controller): void
    {
        $this->endpoints[$path] = $controller;
    }
}
