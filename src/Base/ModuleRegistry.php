<?php
declare(strict_types=1);

namespace Cyndaron\Base;

use Cyndaron\Calendar\CalendarAppointmentsProvider;
use Cyndaron\Module\UrlProvider;
use Cyndaron\PageManager\PageManagerTab;
use Cyndaron\Routing\Controller;
use Cyndaron\User\Module\UserMenuItem;
use function in_array;
use function Safe\class_implements;

final class ModuleRegistry
{
    /** @var array<string, class-string<Controller>> */
    public array $controllers = [];

    /** @var array<string, class-string> */
    public array $editorPages = [];

    /** @var array<string, class-string> */
    public array $editorSavePages = [];

    /** @var PageManagerTab[] */
    public array $pageManagerTabs = [];

    /** @var class-string<UrlProvider>[] $urlProviders */
    public array $urlProviders = [];

    /** @var CalendarAppointmentsProvider[] */
    public array $calendarAppointmentsProviders;

    /** @var UserMenuItem[] */
    public array $userMenuItems = [];

    /**
     * @param string $module
     * @param class-string<Controller> $className
     * @return void
     */
    public function addController(string $module, string $className): void
    {
        $this->controllers[$module] = $className;
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
}
