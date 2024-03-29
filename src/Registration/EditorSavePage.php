<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\Imaging\ImageExtractor;
use Cyndaron\Request\RequestParameters;
use Cyndaron\User\User;
use function assert;

final class EditorSavePage extends \Cyndaron\Editor\EditorSavePage
{
    public function __construct(
        private readonly RequestParameters $post,
        private readonly ImageExtractor $imageExtractor,
    ) {
    }

    public function save(int|null $id): int
    {
        $event = new Event($id);
        $event->loadIfIdIsSet();
        $event->name = $this->post->getHTML('titel');
        $event->description = $this->imageExtractor->process($this->post->getHTML('artikel'));
        $event->descriptionWhenClosed = $this->imageExtractor->process($this->post->getHTML('descriptionWhenClosed'));
        $event->openForRegistration = $this->post->getBool('openForRegistration');
        $event->registrationCost0 = $this->post->getFloat('registrationCost0');
        $event->registrationCost1 = $this->post->getFloat('registrationCost1');
        $event->registrationCost2 = $this->post->getFloat('registrationCost2');
        $event->registrationCost3 = $this->post->getFloat('registrationCost3');
        $event->lunchCost = $this->post->getFloat('lunchCost');
        $event->maxRegistrations = $this->post->getInt('maxRegistrations');
        $event->numSeats = $this->post->getInt('numSeats');
        $event->requireApproval = $this->post->getBool('requireApproval');
        $event->hideRegistrationFee = $this->post->getBool('hideRegistrationFee');
        $event->performedPiece = $this->post->getHTML('performedPiece');
        $event->termsAndConditions = $this->post->getHTML('termsAndConditions');

        if ($event->save())
        {
            User::addNotification('Evenement opgeslagen.');
        }
        else
        {
            User::addNotification('Fout bij opslaan evenement');
        }

        assert($event->id !== null);
        $this->returnUrl = '/event/register/' . $event->id;
        return $event->id;
    }
}
