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
