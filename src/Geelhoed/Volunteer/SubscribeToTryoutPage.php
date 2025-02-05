<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\Geelhoed\Tryout\Tryout;
use Cyndaron\Geelhoed\Tryout\TryoutHelpType;
use Cyndaron\Geelhoed\Tryout\TryoutRepository;
use Cyndaron\Page\Page;
use Cyndaron\User\CSRFTokenHandler;
use Cyndaron\View\Template\ViewHelpers;

final class SubscribeToTryoutPage extends Page
{
    public function __construct(Tryout $tryout, TryoutRepository $tryoutRepository, CSRFTokenHandler $tokenHandler)
    {
        $title = 'Inschrijven voor tryout-toernooi ' . ViewHelpers::filterDutchDate($tryout->start);
        $numRounds = $tryout->getNumRounds();
        $status = $tryoutRepository->getStatus($tryout);
        $this->title = $title;
        $this->addScript('/src/Geelhoed/Volunteer/js/SubscribeToTryoutPage.js');
        $this->addTemplateVars([
            'event' => $tryout,
            'numRounds' => $numRounds,
            'helpTypes' => TryoutHelpType::getFriendlyNames(),
            'fullTypes' => $status->fullTypes,
            'fullRounds' => $status->fullRounds,
            'csrfToken' => $tokenHandler->get('vrijwilligers', 'inschrijven-voor-tryout'),
        ]);
    }
}
