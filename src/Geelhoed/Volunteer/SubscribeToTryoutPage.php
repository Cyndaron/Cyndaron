<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Geelhoed\Tryout\TryoutHelpType;
use Cyndaron\Page\Page;
use Cyndaron\User\User;
use Cyndaron\Util\Util;
use Cyndaron\View\Template\ViewHelpers;

final class SubscribeToTryoutPage extends Page
{
    public function __construct(Tryout $event)
    {
        $json = $event->getJsonData();
        $title = 'Inschrijven voor tryout-toernooi ' . ViewHelpers::filterDutchDate($event->start);
        $numRounds = $event->getTryoutNumRounds();
        $status = $event->getTryoutStatus();
        parent::__construct($title);
        $this->addScript('/src/Geelhoed/Volunteer/js/SubscribeToTryoutPage.js');
        $this->addTemplateVars([
            'event' => $event,
            'numRounds' => $numRounds,
            'helpTypes' => TryoutHelpType::getFriendlyNames(),
            'fullTypes' => $status->fullTypes,
            'fullRounds' => $status->fullRounds,
            'csrfToken' => User::getCSRFToken('vrijwilligers', 'inschrijven-voor-tryout'),
        ]);
    }
}
