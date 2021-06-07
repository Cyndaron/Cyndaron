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
use Cyndaron\User\User;
use DateTimeInterface;
use function count;
use function min;

final class ReserveStep3Page extends Page
{
    public function __construct(Hour $hour, DateTimeInterface $date)
    {
        parent::__construct('Reserveren');

        $reservations = Reservation::fetchAll(['hourId = ?', 'date = ?'], [$hour->id, $date->format('Y-m-d')]);
        $leftoverPlaces = $hour->capacity - count($reservations);

        $this->addTemplateVars([
            'csrfToken' => User::getCSRFToken('reservation', 'step-last'),
            'date' => $date->format('Y-m-d'),
            'hour' => $hour,
            'maxNames' => min(5, $leftoverPlaces),
        ]);
    }
}
