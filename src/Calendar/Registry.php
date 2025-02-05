<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\DBAL\GenericRepository;

final class Registry
{
    public function __construct(
        private readonly ModuleRegistry $moduleRegistry,
        private readonly GenericRepository $repository
    ) {
    }

    /**
     * @return CalendarAppointment[]
     */
    public function getAllAppointments(): array
    {
        $appointments = [];
        foreach ($this->moduleRegistry->calendarAppointmentsProviders as $provider)
        {
            foreach ($provider->getAppointments($this->repository) as $appointment)
            {
                $appointments[] = $appointment;
            }
        }

        return $appointments;
    }
}
