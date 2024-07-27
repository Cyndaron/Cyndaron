<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2;

use Cyndaron\Module\Routes;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\OpenRCT2\Downloads\DownloadController;
use Cyndaron\OpenRCT2\Downloads\DownloadProcessor;

final class Module implements Routes, WithTextPostProcessors
{
    public function routes(): array
    {
        return [
            'download' =>  DownloadController::class,
        ];
    }

    public function getTextPostProcessors(): array
    {
        return [DownloadProcessor::class];
    }
}
