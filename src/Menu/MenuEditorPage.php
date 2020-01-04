<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Page;
use Cyndaron\User\User;
use Cyndaron\Widget\Modal;
use Cyndaron\Widget\Toolbar;

class MenuEditorPage extends Page
{
    public function __construct()
    {
        parent::__construct('Menu-editor');
        $this->addScript('/src/Menu/MenuEditorPage.js');

        $this->render([
            'menuItems' => Menu::get(),
        ]);
    }
}