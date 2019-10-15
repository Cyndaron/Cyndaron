<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\DBConnection;
use Cyndaron\Request;
use Cyndaron\User\User;

class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare()
    {
        $event = new Event($this->id);
        $event->loadIfIdIsSet();
        $event->name = Request::unsafePost('titel');
        $event->description = $this->parseTextForInlineImages(Request::unsafePost('artikel'));
        $event->descriptionWhenClosed = Request::unsafePost('descriptionWhenClosed');
        $event->openForRegistration = (bool)Request::post('openForRegistration');
        $event->registrationCost = (float)str_replace(',', '.', Request::post('registrationCost'));
        $event->performedPiece = Request::unsafePost('performedPiece');
        $event->termsAndConditions = Request::unsafePost('termsAndConditions');

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