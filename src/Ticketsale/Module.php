<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;

class Module implements Routes, Datatypes
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
        return [
            'concert' => Datatype::fromArray([
                'singular' => 'Concert',
                'plural' => 'Concerten',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ])
        ];
    }
}