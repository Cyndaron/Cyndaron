<?php
declare(strict_types=1);
namespace Cyndaron\Registration;

use Cyndaron\ModuleInterface;

class Module implements ModuleInterface
{

    public function routes(): array
    {
        return [
            'event' => EventController::class,
            'event-order' =>  OrderController::class,
        ];
    }

    public function dataTypes(): array
    {
        return ['event' =>
            [
                'singular' => 'Evenement',
                'plural' => 'Evenementen',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ]
        ];
    }
}