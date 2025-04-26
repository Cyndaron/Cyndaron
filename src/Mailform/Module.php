<?php
namespace Cyndaron\Mailform;

use Cyndaron\Module\Datatype;
use Cyndaron\Module\Datatypes;
use Cyndaron\Module\Routes;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\TemplateRenderer;
use function usort;

final class Module implements Datatypes, Routes
{
    /**
     * @inheritDoc
     */
    public function dataTypes(): array
    {
        return [
            'mailform' => new Datatype(
                singular: 'Mailformulier',
                plural: 'Mailformulieren',
                editorPage: EditorPage::class,
                editorSave: EditorSave::class,
                pageManagerTab: self::pageManagerTab(...),
            ),
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

    public static function pageManagerTab(TemplateRenderer $templateRenderer, CSRFTokenHandler $tokenHandler, MailformRepository $mailformRepository): string
    {
        $mailforms = $mailformRepository->fetchAll();
        usort($mailforms, static function(Mailform $m1, Mailform $m2): int
        {
            return $m1->name <=> $m2->name;
        });

        $templateVars = [
            'mailforms' => $mailforms,
            'tokenDelete' => $tokenHandler->get('mailform', 'delete'),
        ];
        return $templateRenderer->render('Mailform/PageManagerTab', $templateVars);
    }
}
