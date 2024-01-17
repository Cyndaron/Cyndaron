<?php
declare(strict_types=1);

namespace Cyndaron\Calendar;

interface CalendarAppointment
{
    public function getName(): string;
    public function getDescription(): string;
    public function getLocation(): string;
    public function getStart(): \DateTimeInterface;
    public function getEnd(): \DateTimeInterface;
    public function getUrl(): string|null;
}
