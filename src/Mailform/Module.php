<?php
namespace Cyndaron\Mailform;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\View\Template\Template;

final class Module implements Datatypes, Routes
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
                'pageManagerTab' => self::class . '::pageManagerTab',
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

    public static function pageManagerTab(): string
    {
        $templateVars = ['mailforms' => Mailform::fetchAll([], [], 'ORDER BY name')];
        return (new Template())->render('Mailform/PageManagerTab', $templateVars);
    }
}
