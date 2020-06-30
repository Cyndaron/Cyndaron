<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public const TYPE = 'mailform';

    protected function prepare(RequestParameters $post): void
    {
        $mailform = new Mailform($this->id);
        $mailform->loadIfIdIsSet();
        $mailform->name = $post->getHTML('titel');
        $mailform->email = $post->getEmail('email');
        $mailform->antiSpamAnswer = $post->getAlphaNum('antiSpamAnswer');
        $mailform->sendConfirmation = $post->getBool('sendConfirmation');
        $mailform->confirmationText = $this->parseTextForInlineImages($post->getHTML('artikel'));

        if ($mailform->save())
        {
            User::addNotification('Mailformulier bewerkt.');
        }
        else
        {
            User::addNotification('Opslaan mailformulier mislukt');
        }

        $this->returnUrl = '/pagemanager/mailform';
    }
}
