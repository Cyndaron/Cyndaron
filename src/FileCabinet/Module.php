<?php
declare(strict_types=1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    public function routes(): array
    {
        return [
            'filecabinet' =>  FileCabinetController::class,
        ];
    }
}
