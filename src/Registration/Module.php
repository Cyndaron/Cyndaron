<?php
declare(strict_types=1);
namespace Cyndaron\Registration;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;

class Module implements Routes, Datatypes
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
        return [
            'event' => Datatype::fromArray([
                'singular' => 'Evenement',
                'plural' => 'Evenementen',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ])
        ];
    }
}