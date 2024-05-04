<?php
namespace Cyndaron\Mailform;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\View\Template\TemplateRenderer;

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
                'editorSave' => EditorSave::class,
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

    public static function pageManagerTab(TemplateRenderer $templateRenderer): string
    {
        $templateVars = ['mailforms' => Mailform::fetchAll([], [], 'ORDER BY name')];
        return $templateRenderer->render('Mailform/PageManagerTab', $templateVars);
    }
}
