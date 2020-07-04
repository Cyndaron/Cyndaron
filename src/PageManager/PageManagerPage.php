<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Category\Category;
use Cyndaron\DBConnection;
use Cyndaron\Mailform\Mailform;
use Cyndaron\Page;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\Template\Template;

final class PageManagerPage extends Page
{
    private static array $pageTypes = [];

    public function __construct(string $currentPage)
    {
        $this->addScript('/src/PageManager/PageManagerPage.js');
        parent::__construct('Paginaoverzicht');

        $pageTabs = [];
        foreach (static::$pageTypes as $pageType => $data)
        {
            $pageTabs[$pageType] = $data['name'];
        }

        $pageDef = static::$pageTypes[$currentPage];
        $tabContents = $pageDef['tabDraw']();

        $this->addTemplateVars([
            'pageTabs' => $pageTabs,
            'currentPage' => $currentPage,
            'tabContents' => $tabContents,
        ]);
        if (!empty($pageDef['js']))
        {
            $this->addScript($pageDef['js']);
        }
    }

    /**
     * Adds a tab definition to the page manager.
     *
     * @param array $pageType
     */
    public static function addPageType(array $pageType): void
    {
        static::$pageTypes = array_merge(static::$pageTypes, $pageType);
    }
}
