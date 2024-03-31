<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Page\Page;
use Cyndaron\Url\UrlService;

final class MenuEditorPage extends Page
{
    public function __construct(UrlService $urlService)
    {
        parent::__construct('Menu-editor');
        $this->addScript('/src/Menu/js/MenuEditorPage.js');

        $this->addTemplateVars([
            'menuItems' => Menu::get(),
            'urlService' => $urlService,
        ]);
    }
}
