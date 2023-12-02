<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use function array_merge;

final class Registry
{
    /** @var CalendarAppointmentsProvider[]  */
    private static array $providers = [];

    public static function addProvider(CalendarAppointmentsProvider $provider): void
    {
        self::$providers[] = $provider;
    }

    /**
     * @return CalendarAppointmentsProvider[]
     */
    public static function getProviders(): array
    {
        return self::$providers;
    }

    /**
     * @return CalendarAppointment[]
     */
    public static function getAllAppointments(): array
    {
        $appointments = [];
        foreach (self::$providers as $provider)
        {
            $appointments = array_merge($appointments, $provider->getAppointments());
        }

        return $appointments;
    }
}
