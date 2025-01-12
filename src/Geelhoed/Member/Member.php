<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\MemberGraduation;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\User\User;
use Cyndaron\Util\FileCache;
use Cyndaron\Util\Util;
use function array_filter;
use function array_key_exists;
use function array_reverse;
use function assert;
use function count;
use function explode;
use function implode;
use function is_int;
use function reset;
use function strtolower;
use function trim;
use function uasort;
use function usort;

final class Member extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_members';

    #[DatabaseField]
    public int $userId;
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

    private const HOURS_CACHE_KEY = 'geelhoed-hours-by-member';

    /** @var array<int, Hour[]> */
    private static array $hoursCache = [];
    private static FileCache $hoursCacheHandle;

    /** @var array<int, float> */
    private static array $monthlyFeeCache = [];
    private static FileCache $monthlyFeeCacheHandle;

    public function getProfile(): User
    {
        $profile = User::fetchById($this->userId);
        assert($profile !== null);
        return $profile;
    }

    private function rebuildHoursCache(): void
    {
        $records = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM geelhoed_members_hours') ?: [];
        foreach ($records as $record)
        {
            $memberId = (int)$record['memberId'];
            if (!array_key_exists($memberId, self::$hoursCache))
            {
                self::$hoursCache[$memberId] = [];
            }

            $hourId = (int)$record['hourId'];
            /** @var Hour $hour */
            $hour = Hour::fetchById($hourId);

            self::$hoursCache[$memberId][] = $hour;
        }

        self::$hoursCacheHandle->save(self::$hoursCache);
    }

    private function loadHoursCache(): void
    {
        if (empty(self::$hoursCacheHandle))
        {
            self::$hoursCacheHandle = new FileCache(self::HOURS_CACHE_KEY, [Hour::class]);
            self::$hoursCacheHandle->load(self::$hoursCache);
        }

        if (empty(self::$hoursCache))
        {
            $this->rebuildHoursCache();
        }
    }

    /**
     * @throws \Exception
     * @return Hour[]
     */
    public function getHours(): array
    {
        $this->loadHoursCache();

        return self::$hoursCache[$this->id] ?? [];
    }

    /**
     * @param Hour[] $hours
     */
    public function setHours(array $hours): void
    {
        $this->loadHoursCache();

        assert(is_int($this->id));
        DBConnection::getPDO()->executeQuery('DELETE FROM geelhoed_members_hours WHERE memberId = ?', [$this->id]);

        if (empty($hours))
        {
            return;
        }

        $sql = 'INSERT INTO geelhoed_members_hours(memberId, hourId) VALUES ';
        foreach ($hours as $hour)
        {
            $sql .= "({$this->id}, {$hour->id}), ";
        }

        $sql = trim($sql, ' ,');
        DBConnection::getPDO()->executeQuery($sql);

        self::$hoursCache[$this->id] = $hours;
        self::$hoursCacheHandle->save(self::$hoursCache);
    }

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
        $profile = $this->getProfile();
        return $profile->email ?: $this->parentEmail;
    }

    /**
     * @return MemberGraduation[]
     */
    public function getMemberGraduations(): array
    {
        return MemberGraduation::fetchAllByMember($this);
    }

    public function isSenior(): bool
    {
        $dateOfBirth = $this->getProfile()->dateOfBirth;
        if ($dateOfBirth === null)
        {
            return true;
        }

        $startOfNextQuarter = Util::getStartOfNextQuarter();
        $diff = $startOfNextQuarter->diff($dateOfBirth);
        return $diff->format('%y') >= 15;
    }

    /**
     * @throws \Exception
     * @return Sport[]
     */
    public function getSports(): array
    {
        $sports = [];
        foreach ($this->getHours() as $hour)
        {
            $sport = $hour->getSport();
            $sports[$sport->id] = $sport;
        }

        return $sports;
    }

    public function getHighestGraduation(Sport $sport):Graduation|null
    {
        $graduations = MemberGraduation::fetchAllByMember($this);
        // Results are ordered by date, so the reverse it to start with the highest ones.
        foreach (array_reverse($graduations) as $memberGraduation)
        {
            $graduation = $memberGraduation->getGraduation();
            if ($graduation->getSport()->id === $sport->id)
            {
                return $graduation;
            }
        }

        return null;
    }

    /**
     * Calculate monthly total from the start of the next quarter,
     * without gezinskorting.
     *
     * @throws \Exception
     * @return float
     */
    public function getMonthlyFeeRaw(): float
    {
        if ($this->freeParticipation)
        {
            return 0.00;
        }
        if ($this->temporaryStop)
        {
            return 0.00;
        }
        $sports = $this->getSports();
        if (count($sports) === 0)
        {
            return 0.00;
        }
        $isSenior = $this->isSenior();
        $discount = $this->discount;
        if (count($sports) === 1)
        {
            $sport = reset($sports);
            if ($isSenior)
            {
                return $sport->seniorFee - $discount;
            }

            return $sport->juniorFee - $discount;
        }

        $highestFee = 0.00;
        foreach ($sports as $sport)
        {
            $fee = $isSenior ? $sport->seniorFee : $sport->juniorFee;
            if ($fee > $highestFee)
            {
                $highestFee = $fee;
            }
        }

        return ($highestFee + 5.00) - $discount;
    }

    private function getMonthlyFeeUncached(): float
    {
        $membersOnSameAccount = self::fetchAll(['iban = ?'], [$this->iban]);

        if (count($membersOnSameAccount) === 1)
        {
            return $this->getMonthlyFeeRaw();
        }

        $feesOnThisAccount = [];
        foreach ($membersOnSameAccount as $memberOnAccount)
        {
            $feesOnThisAccount[$memberOnAccount->id] = $memberOnAccount->getMonthlyFeeRaw();
        }

        // Sort fees, with the highest coming first.
        uasort($feesOnThisAccount, static function(float $fee1, float $fee2)
        {
            return $fee2 <=> $fee1;
        });

        $currentMemberIndex = 0;
        foreach ($feesOnThisAccount as $memberId => $fee)
        {
            if ($memberId === $this->id)
            {
                return match ($currentMemberIndex)
                {
                    0 => $fee,
                    1 => $fee * 0.9,
                    2 => $fee * 0.8,
                    3 => $fee * 0.7,
                    default => $fee * 0.6,
                };
            }
            $currentMemberIndex++;
        }

        // Shouldn't happen, but in case it does, assume full price.
        return $this->getMonthlyFeeRaw();
    }

    private static function rebuildMonthlyFeeCache(): void
    {
        if (empty(self::$monthlyFeeCacheHandle))
        {
            self::$monthlyFeeCacheHandle = new FileCache('member_monthly_fee_cache', []);
            self::$monthlyFeeCacheHandle->load(self::$monthlyFeeCache);
        }

        self::$monthlyFeeCache = [];
        $members = self::fetchAll();
        foreach ($members as $member)
        {
            assert(is_int($member->id));
            $fee = $member->getMonthlyFeeUncached();
            self::$monthlyFeeCache[$member->id] = $fee;
        }

        self::$monthlyFeeCacheHandle->save(self::$monthlyFeeCache);
    }

    /**
     * Calculate monthly total from the start of the next quarter.
     *
     * @throws \Exception
     * @return float
     */
    public function getMonthlyFee(): float
    {
        if (empty(self::$monthlyFeeCache))
        {
            self::rebuildMonthlyFeeCache();
        }

        return self::$monthlyFeeCache[$this->id] ?? 0.0;
    }

    /**
     * Calculate quarterly total from the start of the next quarter.
     *
     * @throws \Exception
     * @return float
     */
    public function getQuarterlyFee(): float
    {
        return $this->getMonthlyFee() * 3;
    }

    /**
     * @param Hour $hour
     * @throws \Safe\Exceptions\ArrayException
     * @throws \Safe\Exceptions\ArrayException
     * @return self[]
     */
    public static function fetchAllByHour(Hour $hour): array
    {
        $results = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM `geelhoed_members` WHERE id IN (SELECT memberId FROM geelhoed_members_hours WHERE hourId = ?)', [$hour->id]);
        $ret = [];
        if ($results)
        {
            foreach ($results as $result)
            {
                $obj = new static((int)$result['id']);
                $obj->updateFromArray($result);
                $ret[] = $obj;
            }
        }
        self::sortByName($ret);

        return $ret;
    }

    public static function loadFromProfile(User $profile): self|null
    {
        $results = self::fetchAll(['userId = ?'], [$profile->id]);
        if (count($results) <= 0)
        {
            return null;
        }

        $firstElem = reset($results);
        return $firstElem;
    }

    /**
     * @param User $user
     * @throws \Exception
     * @return self[]
     */
    public static function fetchAllByUser(User $user): array
    {
        /** @var Member[] $ret */
        $ret = [];
        // A logged in member can always control their own membership.
        $ownMember = self::loadFromProfile($user);
        if ($ownMember !== null)
        {
            $ret[] = $ownMember;
        }

        $relatedMembers = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM geelhoed_users_members WHERE userId = ?', [$user->id]) ?: [];
        foreach ($relatedMembers as $relatedMemberArray)
        {
            $member = self::fetchById((int)$relatedMemberArray['memberId']);
            if ($member !== null)
            {
                $ret[] = $member;
            }
        }

        return $ret;
    }

    /**
     * @param User $profile
     * @throws \Exception
     * @return self[]
     */
    public static function fetchAllContestantsByUser(User $profile): array
    {
        return array_filter(self::fetchAllByUser($profile), static function(Member $member)
        {
            return $member->isContestant;
        });
    }

    public function getIbanHolder(): string
    {
        if ($this->ibanHolder !== '')
        {
            return $this->ibanHolder;
        }

        return $this->getProfile()->lastName;
    }

    public function save(): bool
    {
        $result = parent::save();
        if ($result)
        {
            self::rebuildMonthlyFeeCache();
        }
        return $result;
    }

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return static[]
     */
    public static function fetchAllAndSortByName(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        $list = self::fetchAll($where, $args, $afterWhere);
        self::sortByName($list);
        return $list;
    }

    /**
     * @param Member $m1
     * @param Member $m2
     * @return int<-1,1>
     */
    public static function compareByName(self $m1, self $m2): int
    {
        $p1 = $m1->getProfile();
        $p2 = $m2->getProfile();
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

    /**
     * @param self[] $results
     * @return void
     */
    private static function sortByName(array &$results): void
    {
        usort($results, self::compareByName(...));
    }
}
