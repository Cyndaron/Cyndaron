<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Page;
use Cyndaron\User\User;

final class ReserveStep2Page extends Page
{
    public function __construct(Hour $hour)
    {
        parent::__construct('Reserveren');

        $datesInDropdown = [];
        foreach (Reservation::getDatesForHour($hour) as $day)
        {
            $date = $day['date'];

            $key = $date->format('Y-m-d');
            $value = $date->format('d-m-Y');

            $datesInDropdown[$key] = "$value ({$day['leftoverPlaces']} plaatsen vrij)";
        }

        $this->addTemplateVars([
            'csrfToken' => User::getCSRFToken('reservation', 'step-3'),
            'datesInDropdown' => $datesInDropdown,
            'hour' => $hour,
        ]);
    }
}
