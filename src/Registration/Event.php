<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\KnownShortCodes;
use Cyndaron\Util\Setting;

final class Event extends Model
{
    public const TABLE = 'registration_events';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public bool $openForRegistration = true;
    #[DatabaseField]
    public string $description = '';
    #[DatabaseField]
    public string $descriptionWhenClosed = '';
    #[DatabaseField]
    public float $registrationCost0;
    #[DatabaseField]
    public float $registrationCost1;
    #[DatabaseField]
    public float $registrationCost2 = 0.0;
    #[DatabaseField]
    public float $registrationCost3 = 0.0;
    #[DatabaseField]
    public float $lunchCost;
    #[DatabaseField]
    public int $maxRegistrations = 300;
    #[DatabaseField]
    public int $numSeats = 300;
    #[DatabaseField]
    public bool $requireApproval = false;
    #[DatabaseField]
    public bool $hideRegistrationFee = false;
    #[DatabaseField]
    public string $performedPiece = '';
    #[DatabaseField]
    public string $termsAndConditions = '';

    /**
     * Get answer to antispam question.
     *
     * @return string
     */
    public function getAntispam(): string
    {
        switch (Setting::get(BuiltinSetting::SHORT_CODE))
        {
            case KnownShortCodes::VOV:
                return 'Vlissingen';
        }

        return 'Scratch';
    }
}
