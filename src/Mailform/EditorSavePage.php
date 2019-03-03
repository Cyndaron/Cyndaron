<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare()
    {
        $title = Request::unsafePost('titel');
        $confirmation = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $sendConfirmation = (int)Request::unsafePost('sendConfirmation');
        $email = Request::unsafePost('email');
        $antiSpamAnswer = Request::unsafePost('antiSpamAnswer');

        if ($this->id > 0) // Existing mail form, edit.
        {
            DBConnection::doQuery('UPDATE mailformulieren SET naam = ?, mailadres = ?, antispamantwoord = ?, send_confirmation = ?, confirmation_text = ? WHERE id = ?', [
                $title, $email, $antiSpamAnswer, $sendConfirmation, $confirmation, $this->id
            ]);
        }
        else
        {
            DBConnection::doQuery('INSERT INTO mailformulieren(naam, mailadres, antispamantwoord, send_confirmation, confirmation_text) VALUES(?, ?, ?, ?, ?)', [
                $title, $email, $antiSpamAnswer, $sendConfirmation, $confirmation
            ]);
        }

        User::addNotification('Mailformulier bewerkt.');
        $this->returnUrl = '/pagemanager/mailform';
    }
}