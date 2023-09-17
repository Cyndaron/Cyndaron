<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\Page\Page;

final class MenuEditorPage extends Page
{
    public function __construct()
    {
        parent::__construct('Menu-editor');
        $this->addScript('/src/Menu/js/MenuEditorPage.js');

        $this->addTemplateVars([
            'menuItems' => Menu::get(),
        ]);
    }
}
