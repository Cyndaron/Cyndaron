<?php
declare(strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Pagina;

require_once __DIR__ . '/../../check.php';

class MenuEditorPage extends Pagina
{
    public function __construct()
    {
        parent::__construct('Menu-editor');

        $this->toonPrepagina();
        $this->voegScriptToe('src/Menu/MenuEditorPage.js');

        $menu = MenuModel::get();
        include __DIR__ . '/MenuEditorPageTemplate.php';

        $this->toonPostPagina();
    }
}