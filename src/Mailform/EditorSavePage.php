<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

use Cyndaron\DBConnection;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'mailform';

    protected function prepare(RequestParameters $post)
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
            $errorInfo = var_export(DBConnection::errorInfo(), true);
            User::addNotification('Opslaan mailformulier mislukt: ' . $errorInfo);
        }

        $this->returnUrl = '/pagemanager/mailform';
    }
}