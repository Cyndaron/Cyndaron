<?php
/*
 * Copyright © 2009-2020, Michael Steenbeek
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
 * WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
 * MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
 * ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
 * WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
 * ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
 * OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Page;
use Cyndaron\User\User;

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
            if (count($availableDates) === 0)
            {
                continue;
            }

            $hoursSelect[$hour->id] = "{$location->getName()} {$hour->getRange()}";
        }

        $this->addTemplateVars([
            'csrfToken' => User::getCSRFToken('reservation', 'step-2'),
            'hoursSelect' => $hoursSelect,
        ]);
    }
}
