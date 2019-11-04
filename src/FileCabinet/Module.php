<?php
declare(strict_types=1);

namespace Cyndaron\FileCabinet;

use Cyndaron\Module\Routes;

class Module implements Routes
{
    public function routes(): array
    {
        return [
            'file-cabinet' =>  FileCabinetController::class,
        ];
    }
}