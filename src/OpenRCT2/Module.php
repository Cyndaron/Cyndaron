<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\Module\WithTextPostProcessors;
use Cyndaron\OpenRCT2\Downloads\APICall;
use Cyndaron\OpenRCT2\Downloads\DownloadController;
use Cyndaron\OpenRCT2\Downloads\DownloadProcessor;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;

final class Module implements Routes, WithTextPostProcessors, Datatypes
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

    public function dataTypes(): array
    {
        return [
            'apicall' => new Datatype(
                singular: 'API-call',
                plural: 'API-calls',
                pageManagerTab: self::pageManagerTab(...),
            ),
        ];
    }

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        return $templateRenderer->render('OpenRCT2/Downloads/PageManagerTab', [
            'tokenHandler' => $tokenHandler,
        ]);
    }
}
