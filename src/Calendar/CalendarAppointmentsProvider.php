<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

interface CalendarAppointmentsProvider
{
    /**
     * @return CalendarAppointment[]
     */
    public function getAppointments(): array;
}
