<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Page\Page;
use Cyndaron\Url\UrlService;
use function usort;

final class MenuEditorPage extends Page
{
    public function __construct(UrlService $urlService)
    {
        parent::__construct('Menu-editor');
        $this->addScript('/src/Menu/js/MenuEditorPage.js');

        $menuItems = MenuItem::fetchAll();
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
