<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\User\User;
use Cyndaron\Util\Util;
use function explode;
use function implode;
use function strtolower;

final class Member extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_members';

    #[DatabaseField(dbName: 'userId')]
    public User $profile;
    #[DatabaseField]
    public string $parentEmail = '';
    #[DatabaseField]
    public string $phoneNumbers = '';
    #[DatabaseField]
    public bool $isContestant = false;
    #[DatabaseField]
    public string $paymentMethod = 'incasso';
    #[DatabaseField]
    public string $iban = '';
    #[DatabaseField]
    public string $ibanHolder = '';
    #[DatabaseField]
    public bool $paymentProblem = false;
    #[DatabaseField]
    public string $paymentProblemNote = '';
    #[DatabaseField]
    public bool $freeParticipation = false;
    #[DatabaseField]
    public float $discount;
    #[DatabaseField]
    public bool $temporaryStop = false;
    #[DatabaseField]
    public string|null $joinedAt = null;
    #[DatabaseField]
    public string $jbnNumber = '';
    #[DatabaseField]
    public string $jbnNumberLocation = '';

    public const PAYMENT_METHODS = [
        'incasso' => 'Automatische incasso',
        'jsf' => 'Jeugdsportfonds',
        'rekening' => 'Op rekening',
        'leergeld' => 'Stichting Leergeld',
    ];

    /**
     * @return string[]
     */
    public function getPhoneNumbers(): array
    {
        if ($this->phoneNumbers === '')
        {
            return [];
        }
        return explode(',', $this->phoneNumbers);
    }

    /**
     * @param string[] $numbers
     * @return void
     */
    public function setPhoneNumbers(array $numbers): void
    {
        $this->phoneNumbers = implode(',', $numbers);
    }

    public function getEmail(): string
    {
        return $this->profile->email ?: $this->parentEmail;
    }

    public function isSenior(): bool
    {
        $dateOfBirth = $this->profile->dateOfBirth;
        if ($dateOfBirth === null)
        {
            return true;
        }

        $startOfNextQuarter = Util::getStartOfNextQuarter();
        $diff = $startOfNextQuarter->diff($dateOfBirth);
        return $diff->format('%y') >= 15;
    }

    public function getIbanHolder(): string
    {
        if ($this->ibanHolder !== '')
        {
            return $this->ibanHolder;
        }

        return $this->profile->lastName;
    }

    /**
     * @param Member $m1
     * @param Member $m2
     * @return int<-1,1>
     */
    public static function compareByName(self $m1, self $m2): int
    {
        $p1 = $m1->profile;
        $p2 = $m2->profile;
        $lastname = strtolower($p1->lastName) <=> strtolower($p2->lastName);
        if ($lastname !== 0)
        {
            return $lastname;
        }

        $tussenvoegsel = strtolower($p1->tussenvoegsel) <=> strtolower($p2->tussenvoegsel);
        if ($tussenvoegsel !== 0)
        {
            return $tussenvoegsel;
        }

        return strtolower($p1->firstName) <=> strtolower($p2->firstName);
    }
}
