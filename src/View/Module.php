<?php
declare(strict_types=1);

namespace Cyndaron\View;

use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\View\Renderer\YouTubeRenderer;

final class Module implements WithTextPostProcessors
{
    public function getTextPostProcessors(): array
    {
        return [
            YouTubeRenderer::class,
        ];
    }
}
