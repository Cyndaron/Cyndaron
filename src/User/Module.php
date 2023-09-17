<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Module\WithTextPostProcessors;

final class Module implements WithTextPostProcessors
{
    public function getTextPostProcessors(): array
    {
        return [CSRFTokenRenderer::class];
    }
}
