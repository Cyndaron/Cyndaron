<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Reservation;

use Cyndaron\Page;

final class ReserveStepLastPage extends Page
{
    public function __construct()
    {
        parent::__construct('Reserveren');
    }
}
