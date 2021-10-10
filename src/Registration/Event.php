<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\Model;
use Cyndaron\Util\Setting;

final class Event extends Model
{
    public const TABLE = 'registration_events';
    public const TABLE_FIELDS = ['name', 'openForRegistration', 'description', 'descriptionWhenClosed', 'registrationCost0', 'registrationCost1', 'registrationCost2', 'registrationCost3', 'lunchCost', 'maxRegistrations', 'numSeats', 'requireApproval', 'hideRegistrationFee', 'performedPiece', 'termsAndConditions'];

    public string $name = '';
    public bool $openForRegistration = true;
    public string $description = '';
    public string $descriptionWhenClosed = '';
    public float $registrationCost0;
    public float $registrationCost1;
    public float $registrationCost2 = 0.0;
    public float $registrationCost3 = 0.0;
    public float $lunchCost;
    public int $maxRegistrations = 300;
    public int $numSeats = 300;
    public bool $requireApproval = false;
    public bool $hideRegistrationFee = false;

    public string $performedPiece = '';
    public string $termsAndConditions = '';

    /**
     * Get answer to antispam question.
     *
     * @return string
     */
    public function getAntispam(): string
    {
        switch (Setting::get(Setting::ORGANISATION))
        {
            case Setting::VALUE_ORGANISATION_VOV:
            case Setting::VALUE_ORGANISATION_ZCK:
                return 'Vlissingen';
            case Setting::VALUE_ORGANISATION_SBK:
                return 'Mozart';
        }

        return 'Scratch';
    }
}
