<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\Util\DependencyInjectionContainer;
use function assert;
use function is_callable;

final class PageManagerPage extends Page
{
    public function __construct(DependencyInjectionContainer $dic, User $currentUser, string $currentPage, ModuleRegistry $registry)
    {
        $this->addScript('/src/PageManager/js/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');

        $pageTabs = [];
        $firstVisibleType = null;
        foreach ($registry->pageManagerTabs as $tab)
        {
            $pageType = $tab->type;
            if ($currentUser->hasRight("{$pageType}_edit"))
            {
                $pageTabs[$pageType] = $tab->name;
                if ($firstVisibleType === null)
                {
                    $firstVisibleType = $pageType;
                }
            }
        }

        if ($firstVisibleType === null)
        {
            throw new \RuntimeException('Er zijn geen datatypes die u kunt beheren!');
        }
        if (!$currentUser->hasRight("{$currentPage}_edit"))
        {
            $currentPage = $firstVisibleType;
        }

        $tab = $registry->pageManagerTabs[$currentPage];
        $drawingFunction = $tab->tabDraw;
        assert(is_callable($drawingFunction));
        /** @var string $tabContents */
        $tabContents = $dic->callStaticMethodWithDependencyInjection($drawingFunction);

        $this->addTemplateVars([
            'pageTabs' => $pageTabs,
            'currentPage' => $currentPage,
            'tabContents' => $tabContents,
        ]);
        if (!empty($tab->js))
        {
            $this->addScript($tab->js);
        }
    }
}
