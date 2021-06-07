<?php
declare(strict_types=1);

namespace Cyndaron\Minecraft;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    public function routes(): array
    {
        return [
            'minecraft' =>  MinecraftController::class,
        ];
    }
}
