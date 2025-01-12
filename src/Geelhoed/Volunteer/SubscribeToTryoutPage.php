<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Geelhoed\Tryout\TryoutHelpType;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\ViewHelpers;

final class SubscribeToTryoutPage extends Page
{
    public function __construct(Tryout $event, CSRFTokenHandler $tokenHandler)
    {
        $title = 'Inschrijven voor tryout-toernooi ' . ViewHelpers::filterDutchDate($event->start);
        $numRounds = $event->getTryoutNumRounds();
        $status = $event->getTryoutStatus();
        $this->title = $title;
        $this->addScript('/src/Geelhoed/Volunteer/js/SubscribeToTryoutPage.js');
        $this->addTemplateVars([
            'event' => $event,
            'numRounds' => $numRounds,
            'helpTypes' => TryoutHelpType::getFriendlyNames(),
            'fullTypes' => $status->fullTypes,
            'fullRounds' => $status->fullRounds,
            'csrfToken' => $tokenHandler->get('vrijwilligers', 'inschrijven-voor-tryout'),
        ]);
    }
}
