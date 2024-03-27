<?php
declare(strict_types=1);

namespace Cyndaron\Base;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    public function routes(): array
    {
        // Endpoints for very basic stuff, or for stuff that is not yet modular.
        return [
            'editor' => \Cyndaron\Editor\EditorController::class,
            'error' => \Cyndaron\Error\ErrorController::class,
            'menu' => \Cyndaron\Menu\MenuController::class,
            'menu-editor' => \Cyndaron\Menu\MenuEditorController::class,
            'pagemanager' => \Cyndaron\PageManager\PageManagerController::class,
            'system' => \Cyndaron\System\SystemController::class,
        ];
    }
}
