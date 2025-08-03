<?php
/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\RCTspace;

use Cyndaron\Module\Routes;
use Cyndaron\RCTspace\Downloads\DownloadsController;
use Cyndaron\RCTspace\RideExchange\RideExchangeController;

final class Module implements Routes
{
    public function routes(): array
    {
        return [
            'downloads' =>  DownloadsController::class,
            'ride-exchange' =>  RideExchangeController::class,
        ];
    }
}
