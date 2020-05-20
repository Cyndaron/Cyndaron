<?php
namespace Cyndaron\Mailform;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\PageManager\PageManagerPage;

class Module implements Datatypes, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'mailform' => Datatype::fromArray([
                'singular' => 'Mailformulier',
                'plural' => 'Mailformulieren',
                'pageManagerTab' => PageManagerPage::class . '::showMailforms',
                'editorPage' => EditorPage::class,
                'editorSavePage' => EditorSavePage::class,
            ]),
        ];
    }

    /**
     * @inheritDoc
     */
    public function routes(): array
    {
        return [
            'mailform' => MailformController::class,
        ];
    }
}
