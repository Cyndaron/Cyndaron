<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;

class Event extends Model
{
    const TABLE = 'registration_events';
    const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost', 'maxRegistrations', 'numSeats'];

    private const ANTISPAM_ANSWER = 'Scratch';

    public $name = '';
    public $openForRegistration = true;
    public $description = '';
    public $descriptionWhenClosed = '';
    public $registrationCost;
    public $maxRegistrations = 300;
    public $numSeats = 300;

    public function getAntispam(): string
    {
        return static::ANTISPAM_ANSWER;
    }
}