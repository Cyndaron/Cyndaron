<?php
declare(strict_types=1);

namespace Cyndaron\Routing;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\DBConnection;
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
use Cyndaron\Page\PageRenderer;
use Cyndaron\Page\SimplePage;
use Cyndaron\PageManager\PageManagerTab;
use Cyndaron\Request\QueryBits;
use Cyndaron\Request\UrlInfo;
use Cyndaron\Translation\Translator;
use Cyndaron\Url\UrlService;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\User\Module\UserMenuProvider;
use Cyndaron\User\User;
use Cyndaron\User\UserSession;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\MailFactory;
use Cyndaron\Util\Setting;
use Cyndaron\Util\SettingsRepository;
use Cyndaron\Util\UserSafeError;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\TemplateRendererFactory;
use PDO;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Mime\Address;
use Throwable;
use function array_merge;
use function defined;
use function Safe\error_log;
use function set_exception_handler;
use function str_starts_with;
use function assert;

final class Kernel
{
    public const HEADERS_DO_NOT_CACHE = [
        'cache-control' => 'no-cache, no-store, must-revalidate',
        'pragma' => 'no-cache',
        'expires' => 0,
    ];

    private function setExceptionHandler(LoggerInterface $logger, PageRenderer $pageRenderer, Translator $translator): void
    {
        set_exception_handler(static function(Throwable $t) use ($logger, $pageRenderer, $translator)
        {
            $logger->error((string)$t);
            $page = new SimplePage($translator->get('Fout'), $translator->get('Er ging iets mis bij het laden van deze pagina!'));
            $response = $pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();
        });
    }

    private function buildMultilogger(SettingsRepository $settings, MailFactory $mailFactory): MultiLogger
    {
        $fileLogger = new FileLogger(ROOT_DIR . '/var/log/cyndaron.log');
        $mailRecipient = $settings->get(BuiltinSetting::MAIL_LOG_RECIPIENT);
        if (!empty($mailRecipient))
        {
            $from = $mailFactory->getNoreplyAddress();
            $to = new Address($mailRecipient);
            $mailLogger = new MailLogger($from, $to, $settings->get(BuiltinSetting::SITE_NAME));
            return new MultiLogger($fileLogger, $mailLogger);
        }

        return new MultiLogger($fileLogger);
    }

    private function buildDIC(Connection $connection, ModuleRegistry $registry, Request $request, UserSession $userSession): DependencyInjectionContainer
    {
        $dic = new DependencyInjectionContainer();

        $dic->add($connection);
        $dic->add($connection, PDO::class);

        $dic->add($registry);
        $dic->add($request);
        $dic->add($userSession);

        $urlService = new UrlService($connection, $request->getRequestUri(), $registry->urlProviders);
        $dic->add($urlService);

        $templateRenderer = TemplateRendererFactory::createTemplateRenderer($registry->templateRoots);
        $dic->add($templateRenderer);

        $tokenHandler = new CSRFTokenHandler($userSession->getSymfonySession());
        $dic->add($tokenHandler);

        $urlInfo = UrlInfo::fromRequest($request);
        $dic->add($urlInfo);

        $language = Setting::get(BuiltinSetting::LANGUAGE);
        $translator = new Translator($language);
        $dic->add($translator);

        $mailFactory = $dic->createClassWithDependencyInjection(MailFactory::class);
        $settings = $dic->createClassWithDependencyInjection(SettingsRepository::class);
        $multiLogger = $this->buildMultilogger($settings, $mailFactory);
        $dic->add($multiLogger, LoggerInterface::class);

        $pageRenderer = $dic->createClassWithDependencyInjection(PageRenderer::class);

        $this->setExceptionHandler($multiLogger, $pageRenderer, $translator);

        return $dic;
    }

    private function route(DependencyInjectionContainer $dic): Response
    {
        $request = $dic->get(Request::class);
        $pageRenderer = $dic->get(PageRenderer::class);
        $translator = $dic->get(Translator::class);

        try
        {
            $router = new Router($dic, $pageRenderer);
            return $router->route($request);
        }
        catch (UserSafeError $error)
        {
            $isApiCall = str_starts_with($request->getRequestUri(), '/api');
            if ($isApiCall)
            {
                return new JsonResponse(['error' => $error->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
            }

            $page = new SimplePage($translator->get('Fout'), $error->getMessage());
            return $pageRenderer->renderResponse($page, status: Response::HTTP_INTERNAL_SERVER_ERROR);
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

            $page = new SimplePage($translator->get('Fout'), $translator->get('Er ging iets mis bij het laden van deze pagina!'));
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
        return "{$upgradeInsecureRequests} frame-ancestors 'self'; default-src 'none'; base-uri 'none'; child-src 'none'; connect-src 'self'; font-src 'self'; frame-src 'self' youtube.com *.youtube.com youtu.be docs.google.com; img-src 'self' https: data:;  manifest-src 'self'; media-src 'self' data: https:; object-src 'none'; script-src $scriptSrc; style-src 'self' 'unsafe-inline'";
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
            \Cyndaron\Location\Module::class,
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
                    if (isset($definition->editorSave))
                    {
                        $registry->addEditorSaveClass($dataTypeName, $definition->editorSave);
                    }
                    if ($definition->pageManagerTab !== null)
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
                        $registry->addInternalLinkType($moduleClass);
                    }
                }
            }

            if ($module instanceof UserMenuProvider)
            {
                foreach ($module->getUserMenuItems($currentUser) as $userMenuItem)
                {
                    $registry->addUserMenuItem($userMenuItem);
                }
            }
            if ($module instanceof Templated)
            {
                $registry->addTemplateRoot($module->getTemplateRoot());
            }
            if ($module instanceof WithPageProcessors)
            {
                foreach ($module->getPageprocessors() as $processor)
                {
                    $registry->addPageProcessor(new $processor());
                }
            }
            if ($module instanceof WithTextPostProcessors)
            {
                foreach ($module->getTextPostProcessors() as $processor)
                {
                    $registry->addTextPostProcessor($processor);
                }
            }
            if ($module instanceof CalendarAppointmentsProvider)
            {
                $registry->addCalendarAppointmentsProvider($module);
            }
        }

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

    public function handle(Request $request): Response
    {
        if ($request->hasPreviousSession())
        {
            $symSession = $request->getSession();
            assert($symSession instanceof FlashBagAwareSessionInterface);
        }
        else
        {
            $storage = new NativeSessionStorage([
                'cookie_secure' => 'auto',
                'cookie_samesite' => Cookie::SAMESITE_LAX,
            ]);
            $symSession = new Session($storage);
        }

        $userSession = new UserSession($symSession);
        if ($userSession->hasStarted())
        {
            $userSession->start();
        }

        $user = $userSession->getProfile();
        $registry = $this->loadModules($user);
        $connection = DBConnection::getPDO();
        $dic = $this->buildDIC($connection, $registry, $request, $userSession);
        $response = $this->route($dic);
        $queryBits = $dic->tryGet(QueryBits::class);
        $cspHeader = $this->getCSPHeader((bool)$request->server->get('HTTPS'), $queryBits);
        $response->headers->set('Content-Security-Policy', $cspHeader);

        return $response;
    }
}
