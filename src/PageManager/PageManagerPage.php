<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Closure;
use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Page\Page;
use Cyndaron\Translation\Translator;
use Cyndaron\User\User;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\RuntimeUserSafeError;
use function array_key_exists;
use function assert;
use function is_callable;

final class PageManagerPage extends Page
{
    public function __construct(DependencyInjectionContainer $dic, User $currentUser, string $currentPage, ModuleRegistry $registry)
    {
        if (!array_key_exists($currentPage, $registry->pageManagerTabs))
        {
            throw new RuntimeUserSafeError('Type does not exist!');
        }

        $t = $dic->get(Translator::class);
        $this->addScript('/src/PageManager/js/PageManagerPage.js');
        $this->title = $t->get('Pagina-overzicht');

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
            throw new RuntimeUserSafeError('Er zijn geen datatypes die u kunt beheren!');
        }
        if (!$currentUser->hasRight("{$currentPage}_edit"))
        {
            $currentPage = $firstVisibleType;
        }

        $tab = $registry->pageManagerTabs[$currentPage];
        $drawingFunction = $tab->tabDraw;
        assert($drawingFunction instanceof Closure);
        /** @var string $tabContents */
        $tabContents = $dic->callClosureWithDependencyInjection($drawingFunction);

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
