<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Page\Page;

final class CalendarIndexPage extends Page
{
    public function __construct()
    {
        parent::__construct('Agenda');

        $this->addTemplateVars([
            'appointments' => Registry::getAllAppointments(),
        ]);
    }
}
