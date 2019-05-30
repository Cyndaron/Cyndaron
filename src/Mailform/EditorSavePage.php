<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    const TYPE = 'mailform';

    protected function prepare()
    {
        $mailform = new Mailform($this->id);
        $mailform->loadIfIdIsSet();
        $mailform->name = Request::unsafePost('titel');
        $mailform->email = Request::unsafePost('email');
        $mailform->antiSpamAnswer = Request::unsafePost('antiSpamAnswer');
        $mailform->sendConfirmation = (bool)Request::unsafePost('sendConfirmation');;
        $mailform->confirmationText = $this->parseTextForInlineImages(Request::unsafePost('artikel'));

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