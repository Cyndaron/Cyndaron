<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

use Cyndaron\User\User;
use Cyndaron\Util\Util;
use Cyndaron\View\Page;
use Cyndaron\View\Template\ViewHelpers;
use DateTimeImmutable;

final class SubscribeToTryoutPage extends Page
{
    public function __construct(Event $event)
    {
        $json = $event->getJsonData();
        $startDate = \Safe\DateTimeImmutable::createFromFormat(Util::SQL_DATE_TIME_FORMAT, $event->start);
        $title = 'Inschrijven voor tryout-toernooi ' . ViewHelpers::filterDutchDate($startDate);
        $numRounds = $event->getTryoutNumRounds();
        $status = $event->getTryoutStatus();
        parent::__construct($title);
        $this->addScript('/src/Geelhoed/Volunteer/js/SubscribeToTryoutPage.js');
        $this->addTemplateVars([
            'event' => $event,
            'numRounds' => $numRounds,
            'helpTypes' => TryoutHelpType::getFriendlyNames(),
            'fullTypes' => $status['fullTypes'],
            'fullRounds' => $status['fullRounds'],
            'csrfToken' => User::getCSRFToken('vrijwilligers', 'inschrijven-voor-tryout'),
        ]);
    }
}
