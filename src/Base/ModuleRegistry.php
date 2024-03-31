<?php
declare(strict_types=1);

namespace Cyndaron\Base;

use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\Module\Linkable;
use Cyndaron\Module\TemplateRoot;
use Cyndaron\Module\TextPostProcessor;
use Cyndaron\Module\UrlProvider;
use Cyndaron\Page\Module\PagePreProcessor;
use Cyndaron\PageManager\PageManagerTab;
use Cyndaron\Request\RequestMethod;
use Cyndaron\Routing\Controller;
use Cyndaron\Routing\Route;
use Cyndaron\Routing\RouteAttribute;
use Cyndaron\User\Module\UserMenuItem;
use ReflectionClass;
use function in_array;
use function rtrim;
use function Safe\class_implements;

final class ModuleRegistry
{
    /** @var array<string, class-string<Controller>> */
    public array $controllers = [];

    /** @var array<string, array<string, array<string, array<int, Route>>>> */
    public array $routes = [];

    /** @var array<string, class-string> */
    public array $editorPages = [];

    /** @var array<string, class-string> */
    public array $editorSavePages = [];

    /** @var class-string<Linkable>[] */
    public array $internalLinkTypes = [];

    /** @var PageManagerTab[] */
    public array $pageManagerTabs = [];

    /** @var class-string<UrlProvider>[] $urlProviders */
    public array $urlProviders = [];

    /** @var CalendarAppointmentsProvider[] */
    public array $calendarAppointmentsProviders;

    /** @var UserMenuItem[] */
    public array $userMenuItems = [];

    /** @var PagePreProcessor[] $pageProcessors */
    public array $pageProcessors = [];

    /** @var class-string<TextPostProcessor>[] $textPostProcessors */
    public array $textPostProcessors = [];

    /** @var array<string, string> */
    public array $templateRoots = [];

    /** @var class-string[] */
    public array $classesToAutowire = [];

    /**
     * @param string $module
     * @param class-string<Controller> $className
     * @return void
     */
    public function addController(string $module, string $className): void
    {
        $this->controllers[$module] = $className;
        $rc = new ReflectionClass($className);
        foreach ($rc->getMethods() as $method)
        {
            foreach ($method->getAttributes(RouteAttribute::class) as $attribute)
            {
                /** @var RouteAttribute $instance */
                $instance = $attribute->newInstance();
                $route = new Route(
                    $method->getName(),
                    $instance->level,
                    $instance->right,
                    $instance->skipCSRFCheck
                );
                $apiMethod = (int)$instance->isApiMethod;
                $this->routes[$module][$instance->action][$instance->method->value][$apiMethod] = $route;
            }
        }
    }

    /**
     * @param string $module
     * @param class-string $className
     * @return void
     */
    public function addEditorPage(string $module, string $className): void
    {
        $this->editorPages[$module] = $className;
    }

    /**
     * @param string $module
     * @param class-string $className
     * @return void
     */
    public function addEditorSavePage(string $module, string $className): void
    {
        $this->editorSavePages[$module] = $className;
    }

    /**
     * @param class-string<Linkable> $moduleClass
     * @return void
     */
    public function addInternalLinkType(string $moduleClass): void
    {
        $this->internalLinkTypes[] = $moduleClass;
    }

    public function addPageManagerTab(PageManagerTab $tab): void
    {
        $this->pageManagerTabs[$tab->type] = $tab;
    }

    /**
     * @param class-string<UrlProvider> $class
     */
    public function addUrlProvider(string $urlBase, string $class): void
    {
        if (in_array(UrlProvider::class, class_implements($class), true))
        {
            $this->urlProviders[$urlBase] = $class;
        }
    }

    public function addCalendarAppointmentsProvider(CalendarAppointmentsProvider $provider): void
    {
        $this->calendarAppointmentsProviders[] = $provider;
    }

    public function addUserMenuItem(UserMenuItem $userMenuItem): void
    {
        $this->userMenuItems[] = $userMenuItem;
    }

    public function addPageProcessor(PagePreProcessor $processor): void
    {
        $this->pageProcessors[] = $processor;
    }

    /**
     * @param class-string<TextPostProcessor> $postProcessor
     * @return void
     */
    public function addTextPostProcessor(string $postProcessor): void
    {
        $this->textPostProcessors[] = $postProcessor;
    }

    public function addTemplateRoot(TemplateRoot $templateRoot): void
    {
        $this->templateRoots[$templateRoot->name] = rtrim($templateRoot->root, '/');
    }

    public function getRoute(string $module, string $action, RequestMethod $method, bool $isApiCall): Route|null
    {
        return $this->routes[$module][$action][$method->value][(int)$isApiCall] ?? null;
    }
}
