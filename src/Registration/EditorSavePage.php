<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

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
        $event->registrationCost0 = (float)str_replace(',', '.', Request::post('registrationCost0'));
        $event->registrationCost1 = (float)str_replace(',', '.', Request::post('registrationCost1'));
        $event->registrationCost2 = (float)str_replace(',', '.', Request::post('registrationCost2'));
        $event->lunchCost = (float)str_replace(',', '.', Request::post('lunchCost'));
        $event->maxRegistrations = (int)Request::post('maxRegistrations');
        $event->numSeats = (int)Request::post('numSeats');
        $event->requireApproval = (bool)Request::post('requireApproval');

        if ($event->save())
        {
            User::addNotification('Evenement opgeslagen.');
        }
        else
        {
            $errorInfo = var_export(DBConnection::errorInfo(), true);
            User::addNotification('Fout bij opslaan evenement: ' . $errorInfo);
        }

        $this->returnUrl = '/event/order/' . $event->id;
    }
}