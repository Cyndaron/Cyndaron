<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Page\Page;
use Cyndaron\Url\UrlService;
use function usort;

final class MenuEditorPage extends Page
{
    public function __construct(UrlService $urlService, MenuItemRepository $menuItemRepository)
    {
        $this->title = 'Menu-editor';
        $this->addScript('/src/Menu/js/MenuEditorPage.js');

        $menuItems = $menuItemRepository->fetchAll();
        usort($menuItems, static function(MenuItem $menuItem1, MenuItem $menuItem2)
        {
            return $menuItem1->priority <=> $menuItem2->priority;
        });

        $this->addTemplateVars([
            'menuItems' => $menuItems,
            'urlService' => $urlService,
        ]);
    }
}
