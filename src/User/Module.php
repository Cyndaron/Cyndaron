<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Module\Routes;
use Cyndaron\Module\WithTextPostProcessors;

final class Module implements WithTextPostProcessors, Routes
{
    public function getTextPostProcessors(): array
    {
        return [CSRFTokenRenderer::class];
    }

    public function routes(): array
    {
        return [
            'user' => UserController::class,
        ];
    }
}
