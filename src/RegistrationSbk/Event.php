<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Model;

class Event extends Model
{
    const TABLE = 'registrationsbk_events';
    const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost', 'performedPiece', 'termsAndConditions'];

    private const ANTISPAM_ANSWER = 'Mozart';

    public $name = '';
    /** @var bool */
    public $openForRegistration = true;
    public $description = '';
    public $descriptionWhenClosed = '';
    /** @var float */
    public $registrationCost;

    public $performedPiece = '';

    public $termsAndConditions = '';

    public function getAntispam(): string
    {
        return static::ANTISPAM_ANSWER;
    }
}