<?php
declare(strict_types=1);
namespace Cyndaron\RegistrationSbk;

use Cyndaron\ModuleInterface;

class Module implements ModuleInterface
{

    public function routes(): array
    {
        return [
            'eventSbk' => EventController::class,
            'eventSbk-registration' => RegisterController::class,
        ];
    }

    public function dataTypes(): array
    {
        return ['eventSbk' =>
            [
                'singular' => 'SBK-evenement',
                'plural' => 'SBK-evenementen',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ]
        ];
    }
}