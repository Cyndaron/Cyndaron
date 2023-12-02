<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Module\Routes;

final class Module implements Routes
{
    public function routes(): array
    {
        return [
            'agenda' => CalendarController::class,
        ];
    }
}
