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

final class OverviewPage extends Page
{
    public function __construct()
    {
        parent::__construct('Overzicht reserveringen');


        $this->addTemplateVars([
            'stats' => Reservation::getHoursAndDatesStatistics(),
            'hours' => Hour::fetchAll(),
        ]);
    }
}
