<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\ModuleInterface;

class Module implements ModuleInterface
{

    public function routes(): array
    {
        return [
            'concert' => ConcertController::class,
            'concert-order' => OrderController::class,
        ];
    }

    public function dataTypes(): array
    {
        return ['concert' =>
            [
                'singular' => 'Concert',
                'plural' => 'Concerten',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ]
        ];
    }
}