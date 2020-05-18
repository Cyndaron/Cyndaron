<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

use Cyndaron\Model;

class Event extends Model
{
    public const TABLE = 'registrationsbk_events';
    public const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost', 'performedPiece', 'termsAndConditions'];

    private const ANTISPAM_ANSWER = 'Mozart';

    public string $name = '';
    public bool $openForRegistration = true;
    public string $description = '';
    public string $descriptionWhenClosed = '';
    public float $registrationCost;

    public string $performedPiece = '';

    public string $termsAndConditions = '';

    public function getAntispam(): string
    {
        return static::ANTISPAM_ANSWER;
    }
}