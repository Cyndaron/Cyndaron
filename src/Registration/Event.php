<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;
use Cyndaron\Setting;

class Event extends Model
{
    public const TABLE = 'registration_events';
    public const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost0', 'registrationCost1', 'registrationCost2', 'lunchCost', 'maxRegistrations', 'numSeats', 'requireApproval'];

    public string $name = '';
    public bool $openForRegistration = true;
    public string $description = '';
    public string $descriptionWhenClosed = '';
    public float $registrationCost0;
    public float $registrationCost1;
    public float $registrationCost2 = 0.0;
    public float $lunchCost;
    public int $maxRegistrations = 300;
    public int $numSeats = 300;
    public bool $requireApproval = false;

    /**
     * Get answer to antispam question.
     *
     * @return string
     */
    public function getAntispam(): string
    {
        if (Setting::get('organisation') === 'Vlissingse Oratorium Vereniging')
        {
            return 'Vlissingen';
        }

        return 'Scratch';
    }
}