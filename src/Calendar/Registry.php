<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;

final class Registry
{
    public function __construct(private readonly ModuleRegistry $moduleRegistry)
    {
    }

    /**
     * @return CalendarAppointment[]
     */
    public function getAllAppointments(): array
    {
        $appointments = [];
        foreach ($this->moduleRegistry->calendarAppointmentsProviders as $provider)
        {
            foreach ($provider->getAppointments() as $appointment)
            {
                $appointments[] = $appointment;
            }
        }

        return $appointments;
    }
}
