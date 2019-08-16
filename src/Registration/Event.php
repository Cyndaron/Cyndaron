<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

use Cyndaron\Model;

class Event extends Model
{
    const TABLE = 'registration_events';
    const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost0', 'registrationCost1', 'lunchCost', 'maxRegistrations', 'numSeats'];

    private const ANTISPAM_ANSWER = 'Scratch';

    public $name = '';
    /** @var bool */
    public $openForRegistration = true;
    public $description = '';
    public $descriptionWhenClosed = '';
    /** @var float */
    public $registrationCost0;
    /** @var float */
    public $registrationCost1;
    /** @var float */
    public $lunchCost;
    /** @var int */
    public $maxRegistrations = 300;
    /** @var int */
    public $numSeats = 300;

    public function getAntispam(): string
    {
        return static::ANTISPAM_ANSWER;
    }
}