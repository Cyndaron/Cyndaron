<?php
declare(strict_types=1);
namespace Cyndaron\RegistrationSbk;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;

class Module implements Routes, Datatypes
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
        return [
            'eventSbk' => Datatype::fromArray([
                'singular' => 'SBK-evenement',
                'plural' => 'SBK-evenementen',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
                'pageManagerTab' => Util::class . '::drawPageManagerTab',
            ])
        ];
    }
}