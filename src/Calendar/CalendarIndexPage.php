<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Page\Page;
use function array_filter;
use function usort;

final class CalendarIndexPage extends Page
{
    public function __construct(ModuleRegistry $moduleRegistry)
    {
        parent::__construct('Agenda');

        $calendarRegistry = new Registry($moduleRegistry);
        $appointments = $calendarRegistry->getAllAppointments();
        $now = new \DateTimeImmutable();
        $appointments = array_filter($appointments, static function($appointment) use ($now)
        {
            return $appointment->getEnd() > $now;
        });
        usort($appointments, static function(CalendarAppointment $appointment1, CalendarAppointment $appointment2)
        {
            return $appointment1->getStart() <=> $appointment2->getStart();
        });

        $this->addTemplateVars([
            'appointments' => $appointments,
        ]);
    }
}
