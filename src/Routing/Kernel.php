<?php
/** @noinspection PhpFullyQualifiedNameUsageInspection */
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Editor\EditorController;
use Cyndaron\Logger\FileLogger;
use Cyndaron\Logger\MultiLogger;
use Cyndaron\Mail\MailLogger;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\Routes;
use Cyndaron\Module\Templated;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\Page\Module\WithPageProcessors;
use Cyndaron\Page\Page;
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\PageManager\PageManagerPage;
use Cyndaron\PageManager\PageManagerTab;
use Cyndaron\Request\QueryBits;
use Cyndaron\Url;
use Cyndaron\User\Module\UserMenuProvider;
use Cyndaron\User\User;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\Mail;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Cyndaron\View\Renderer\TextRenderer;
use Cyndaron\View\Template\TemplateFinder;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Mime\Address;
use Throwable;
use function array_merge;
use function assert;
use function defined;
use function filter_input;
use function Safe\error_log;
use function session_start;
use function set_exception_handler;
use function str_starts_with;
use const FILTER_SANITIZE_URL;
use const INPUT_SERVER;

/**
 * Zorgt voor correct doorverwijzen van verzoeken.
 * @package Cyndaron
 */
final class Kernel implements HttpKernelInterface
{
    public const HEADERS_DO_NOT_CACHE = [
        'cache-control' => 'no-cache, no-store, must-revalidate',
        'pragma' => 'no-cache',
        'expires' => 0,
    ];

    private function setExceptionHandler(LoggerInterface $logger, PageRenderer $pageRenderer): void
    {
        set_exception_handler(static function(Throwable $t) use ($logger, $pageRenderer)
        {
            $logger->error((string)$t);
            $page = new SimplePage('Fout', 'Er ging iets mis bij het laden van deze pagina!');
            $response = $pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();
        });
    }

    private function buildDIC(ModuleRegistry $registry, Request $request, User|null $user): DependencyInjectionContainer
    {
        $dic = new DependencyInjectionContainer();
        $dic->add($registry);
        $dic->add($request);

        $pageRenderer = new PageRenderer();
        $dic->add($pageRenderer);
        $pdo = DBConnection::getPDO();
        $dic->add($pdo);
        $dic->add($pdo, \PDO::class);

        $fileLogger = new FileLogger(ROOT_DIR . '/var/log/cyndaron.log');
        $mailRecipient = Setting::get('mail_logRecipient');
        if (!empty($mailRecipient))
        {
            $mailLogger = new MailLogger(Mail::getNoreplyAddress(), new Address($mailRecipient), Setting::get('siteName'));
            $multiLogger = new MultiLogger($fileLogger, $mailLogger);
        }
        else
        {
            $multiLogger = new MultiLogger($fileLogger);
        }
        $dic->add($multiLogger, LoggerInterface::class);
        $this->setExceptionHandler($multiLogger, $pageRenderer);

        if ($user !== null)
        {
            $dic->add($user);
        }

        return $dic;
    }

    private function route(DependencyInjectionContainer $dic): Response
    {
        $request = $dic->get(Request::class);
        $registry = $dic->get(ModuleRegistry::class);
        $pageRenderer = $dic->get(PageRenderer::class);

        try
        {
            $router = new Router($dic, $registry, $pageRenderer);
            return $router->route($request);
        }
        catch (Throwable $t)
        {
            $logger = $dic->tryGet(LoggerInterface::class);
            if (isset($logger))
            {
                $logger->error((string)$t);
            }
            else
            {
                /** @noinspection ForgottenDebugOutputInspection */
                error_log($t->__toString());
            }

            $isApiCall = str_starts_with($request->getRequestUri(), '/api');
            if ($isApiCall)
            {
                return new JsonResponse(null, Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $page = new SimplePage('Fout', 'Er ging iets mis bij het laden van deze pagina!');
            return $pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    private function getCSPHeader(bool $https, QueryBits|null $queryBits): string
    {
        $module = $queryBits !== null ? $queryBits->getString(0) : '';
        $action = $queryBits !== null ? $queryBits->getString(1) : '';
        // Unfortunately, CKeditor still needs inline scripting. Only allow this on editor pages,
        // in order to prevent degrading the security of the rest of the system.
        if ($module === 'editor' || ($module === 'newsletter' && $action === 'compose'))
        {
            $scriptSrc = "'self' 'unsafe-inline'";
        }
        else
        {
            $nonce = self::getScriptNonce();
            $scriptSrc = "'self' 'nonce-{$nonce}' 'strict-dynamic'";
        }

        $upgradeInsecureRequests = $https ? 'upgrade-insecure-requests;' : '';
        return "{$upgradeInsecureRequests} frame-ancestors 'self'; default-src 'none'; base-uri 'none'; child-src 'none'; connect-src 'self'; font-src 'self'; frame-src 'self' youtube.com *.youtube.com youtu.be; img-src 'self' https: data:;  manifest-src 'none'; media-src 'self' data: https:; object-src 'none'; prefetch-src 'self'; script-src $scriptSrc; style-src 'self' 'unsafe-inline'";
    }

    private function loadModules(?User $currentUser): ModuleRegistry
    {
        $registry = new ModuleRegistry();
        $modules = [
            \Cyndaron\Base\Module::class,
            \Cyndaron\Migration\Module::class,
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
                    $registry->addController($path, $controller);
                }
            }
            if ($module instanceof Datatypes)
            {
                foreach ($module->dataTypes() as $dataTypeName => $definition)
                {
                    if (isset($definition->editorPage))
                    {
                        $registry->addEditorPage($dataTypeName, $definition->editorPage);
                    }
                    if (isset($definition->editorSavePage))
                    {
                        $registry->addEditorSavePage($dataTypeName, $definition->editorSavePage);
                    }
                    if (isset($definition->pageManagerTab))
                    {
                        $tab = new PageManagerTab($dataTypeName, $definition->plural, $definition->pageManagerTab, $definition->pageManagerJS ?? null);
                        $registry->addPageManagerTab($tab);
                    }
                    if ($module instanceof UrlProvider)
                    {
                        $registry->addUrlProvider($dataTypeName, $moduleClass);
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
            if ($module instanceof CalendarAppointmentsProvider)
            {
                $registry->addCalendarAppointmentsProvider($module);
            }
        }

        Url::setRegistry($registry);
        return $registry;
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

    public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
    {
        if (empty($_SESSION))
        {
            session_start();
        }

        $user = User::fromSession();
        $registry = $this->loadModules($user);
        $dic = $this->buildDIC($registry, $request, $user);
        $response = $this->route($dic);
        $queryBits = $dic->tryGet(QueryBits::class);
        $cspHeader = $this->getCSPHeader((bool)($_SERVER['HTTPS'] ?? false), $queryBits);
        $response->headers->set('Content-Security-Policy', $cspHeader);

        return $response;
    }
}
