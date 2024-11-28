<?php
declare(strict_types=1);
namespace Cyndaron\Registration;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;

final class Module implements Routes, Datatypes
{
    public function routes(): array
    {
        return [
            'event' => EventController::class,
            'event-registration' =>  RegistrationController::class,
        ];
    }

    public function dataTypes(): array
    {
        return [
            'event' => new Datatype(
                singular: 'Evenement',
                plural: 'Evenementen',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: Util::drawPageManagerTab(...),
            )
        ];
    }
}
