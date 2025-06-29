<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\DBAL\Repository\GenericRepository;

interface CalendarAppointmentsProvider
{
    /**
     * @return CalendarAppointment[]
     */
    public function getAppointments(GenericRepository $repository): array;
}
