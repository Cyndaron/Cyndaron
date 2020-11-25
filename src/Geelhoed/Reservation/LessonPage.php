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
use DateTimeInterface;

final class LessonPage extends Page
{
    public function __construct(Hour $hour, DateTimeInterface $date)
    {
        $reservations = Reservation::fetchAll(['hourId = ?', 'date = ?'], [$hour->id, $date->format('Y-m-d')], 'ORDER BY `created`');
        $location = $hour->getLocation();

        parent::__construct("Reserveringen {$date->format('d-m')} {$location->getName()} {$hour->getRange()}");
        $this->addTemplateVars([
            'hour' => $hour,
            'reservations' => $reservations,
        ]);
    }
}
