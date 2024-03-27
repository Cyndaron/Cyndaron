<?php

namespace Cyndaron\Migration;

use Cyndaron\Module\Routes;

class Module implements Routes
{
    public function routes(): array
    {
        return [
            'migrate' => MigrateController::class,
            'tooncategorie.php' => OldUrlsController::class,
            'toonphoto.php' => OldUrlsController::class,
            'toonphotoalbum.php' => OldUrlsController::class,
            'toonsub.php' => OldUrlsController::class,
            'verwerkmailformulier.php' => OldUrlsController::class,
        ];
    }
}
