<?php
/**
 * Copyright © 2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\View\Page;
use Cyndaron\View\Template\ViewHelpers;
use Cyndaron\User\User;
use Cyndaron\Util\Util;
use function count;

final class ReserveStep1Page extends Page
{
    public function __construct()
    {
        parent::__construct('Reserveren');

        $hours = Hour::fetchAll(['capacity > 0']);
        $hoursSelect = [];
        foreach ($hours as $hour)
        {
            $location = $hour->getLocation();
            $availableDates = Reservation::getDatesForHour($hour);
            $numAvailableDates = count($availableDates);
            $day = ViewHelpers::getDutchWeekday($hour->day);

            $hoursSelect[$hour->id] = "{$location->getName()} {$day} {$hour->getRange()} ({$numAvailableDates} data beschikbaar)";
        }

        $this->addTemplateVars([
            'csrfToken' => User::getCSRFToken('reservation', 'step-2'),
            'hoursSelect' => $hoursSelect,
        ]);
    }
}
