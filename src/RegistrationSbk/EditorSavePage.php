<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\DBConnection;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare(RequestParameters $post)
    {
        $event = new Event($this->id);
        $event->loadIfIdIsSet();
        $event->name = $post->getHTML('titel');
        $event->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $event->descriptionWhenClosed = $this->parseTextForInlineImages($post->getHTML('descriptionWhenClosed'));
        $event->openForRegistration = $post->getBool('openForRegistration');
        $event->registrationCost = $post->getFloat('registrationCost');
        $event->performedPiece = $post->getHTML('performedPiece');
        $event->termsAndConditions = $post->getHTML('termsAndConditions');

        if ($event->save())
        {
            User::addNotification('Evenement opgeslagen.');
        }
        else
        {
            $errorInfo = var_export(DBConnection::errorInfo(), true);
            User::addNotification('Fout bij opslaan evenement: ' . $errorInfo);
        }

        $this->returnUrl = '/eventSbk/register/' . $event->id;
    }
}