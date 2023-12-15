<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Page\Page;
use Cyndaron\User\User;
use function assert;
use function is_callable;

final class PageManagerPage extends Page
{
    /** @var PageManagerTab[] */
    private static array $tabs = [];

    public function __construct(User $currentUser, string $currentPage)
    {
        $this->addScript('/src/PageManager/js/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');

        $pageTabs = [];
        $firstVisibleType = null;
        foreach (self::$tabs as $tab)
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

        $tab = self::$tabs[$currentPage];
        $drawingFunction = $tab->tabDraw;
        assert(is_callable($drawingFunction));
        $tabContents = $drawingFunction($currentUser);

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

    /**
     * Adds a tab definition to the page manager.
     *
     * @param PageManagerTab $tab
     */
    public static function addTab(PageManagerTab $tab): void
    {
        self::$tabs[$tab->type] = $tab;
    }
}
