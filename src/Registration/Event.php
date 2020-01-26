<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;

class Event extends Model
{
    const TABLE = 'registration_events';
    const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost0', 'registrationCost1', 'lunchCost', 'maxRegistrations', 'numSeats'];

    private const ANTISPAM_ANSWER = 'Scratch';

    public string $name = '';
    public bool $openForRegistration = true;
    public string $description = '';
    public string $descriptionWhenClosed = '';
    public float $registrationCost0;
    public float $registrationCost1;
    public float $lunchCost;
    public int $maxRegistrations = 300;
    public int $numSeats = 300;

    /**
     * Get answer to antispam question.
     *
     * @return string
     */
    public function getAntispam(): string
    {
        return static::ANTISPAM_ANSWER;
    }
}