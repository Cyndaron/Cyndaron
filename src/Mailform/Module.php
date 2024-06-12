<?php
namespace Cyndaron\Mailform;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\User\CSRFTokenHandler;
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

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler): string
    {
        $templateVars = [
            'mailforms' => Mailform::fetchAll([], [], 'ORDER BY name'),
            'tokenDelete' => $tokenHandler->get('mailform', 'delete'),
        ];
        return $templateRenderer->render('Mailform/PageManagerTab', $templateVars);
    }
}
