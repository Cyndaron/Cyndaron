<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use Symfony\Component\HttpFoundation\Request;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    protected function prepare(RequestParameters $post, Request $request): void
    {
        $event = new Event($this->id);
        $event->loadIfIdIsSet();
        $event->name = $post->getHTML('titel');
        $event->description = $this->parseTextForInlineImages($post->getHTML('artikel'));
        $event->descriptionWhenClosed = $this->parseTextForInlineImages($post->getHTML('descriptionWhenClosed'));
        $event->openForRegistration = $post->getBool('openForRegistration');
        $event->registrationCost0 = $post->getFloat('registrationCost0');
        $event->registrationCost1 = $post->getFloat('registrationCost1');
        $event->registrationCost2 = $post->getFloat('registrationCost2');
        $event->lunchCost = $post->getFloat('lunchCost');
        $event->maxRegistrations = $post->getInt('maxRegistrations');
        $event->numSeats = $post->getInt('numSeats');
        $event->requireApproval = $post->getBool('requireApproval');
        $event->hideRegistrationFee = $post->getBool('hideRegistrationFee');
        $event->performedPiece = $post->getHTML('performedPiece');
        $event->termsAndConditions = $post->getHTML('termsAndConditions');

        if ($event->save())
        {
            User::addNotification('Evenement opgeslagen.');
        }
        else
        {
            User::addNotification('Fout bij opslaan evenement');
        }

        $this->returnUrl = '/event/register/' . $event->id;
    }
}
