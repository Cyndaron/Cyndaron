<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Closure;
use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Page\Page;
use Cyndaron\Translation\Translator;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use Cyndaron\Util\DependencyInjectionContainer;
use Cyndaron\Util\RuntimeUserSafeError;
use function array_key_exists;
use function assert;

final class PageManagerPage
{
    /**
     * @var PageManagerTab[]
     */
    private array $pageManagerTabs;

    public function __construct(
        private readonly Translator $translator,
        private readonly User $currentUser,
        private readonly UserRepository $userRepository,
        ModuleRegistry $registry
    ) {
        $this->pageManagerTabs = $registry->pageManagerTabs;
    }

    public function createPage(DependencyInjectionContainer $dic, string $currentPage): Page
    {
        if (!array_key_exists($currentPage, $this->pageManagerTabs))
        {
            throw new RuntimeUserSafeError('Type does not exist!');
        }

        $page = new Page();
        $page->title = $this->translator->get('Pagina-overzicht');
        $page->template = 'PageManager/PageManagerPage';
        $page->addScript('/src/PageManager/js/PageManagerPage.js');

        $pageTabs = [];
        $firstVisibleType = null;
        foreach ($this->pageManagerTabs as $tab)
        {
            $pageType = $tab->type;
            if ($this->userRepository->userHasRight($this->currentUser, "{$pageType}_edit"))
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
        if (!$this->userRepository->userHasRight($this->currentUser, "{$currentPage}_edit"))
        {
            $currentPage = $firstVisibleType;
        }

        $tab = $this->pageManagerTabs[$currentPage];
        $drawingFunction = $tab->tabDraw;
        assert($drawingFunction instanceof Closure);
        /** @var string $tabContents */
        $tabContents = $dic->callClosureWithDependencyInjection($drawingFunction);

        $page->addTemplateVars([
            'pageTabs' => $pageTabs,
            'currentPage' => $currentPage,
            'tabContents' => $tabContents,
        ]);
        if (!empty($tab->js))
        {
            $page->addScript($tab->js);
        }

        return $page;
    }
}
