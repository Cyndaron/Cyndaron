<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Member;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Geelhoed\Graduation\Graduation;
use Cyndaron\Geelhoed\Graduation\MemberGraduationRepository;
use Cyndaron\Geelhoed\Hour\Hour;
use Cyndaron\Geelhoed\Hour\HourRepository;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\User\User;
use Cyndaron\Util\FileCache;
use function array_filter;
use function array_key_exists;
use function array_reverse;
use function assert;
use function count;
use function is_int;
use function reset;
use function trim;
use function uasort;
use function usort;

/**
 * @implements RepositoryInterface<Member>
 */
final class MemberRepository implements RepositoryInterface
{
    use RepositoryTrait;

    private const UNDERLYING_CLASS = Member::class;

    private const HOURS_CACHE_KEY = 'geelhoed-hours-by-member';

    /** @var array<int, Hour[]> */
    private static array $hoursCache = [];
    private static FileCache $hoursCacheHandle;

    /** @var array<int, float> */
    private static array $monthlyFeeCache = [];
    private static FileCache $monthlyFeeCacheHandle;


    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly Connection $connection,
        private readonly MemberGraduationRepository $memberGraduationRepository,
        private readonly HourRepository $hourRepository,
    ) {
    }

    public function fetchByProfile(User $profile): Member|null
    {
        $results = $this->fetchAll(['userId = ?'], [$profile->id]);
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
     * @return Member[]
     */
    public function fetchAllByUser(User $user): array
    {
        /** @var Member[] $ret */
        $ret = [];
        // A logged in member can always control their own membership.
        $ownMember = $this->fetchByProfile($user);
        if ($ownMember !== null)
        {
            $ret[] = $ownMember;
        }

        $relatedMembers = $this->connection->doQueryAndFetchAll('SELECT * FROM geelhoed_users_members WHERE userId = ?', [$user->id]) ?: [];
        foreach ($relatedMembers as $relatedMemberArray)
        {
            $member = $this->fetchById((int)$relatedMemberArray['memberId']);
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
     * @return Member[]
     */
    public function fetchAllContestantsByUser(User $profile): array
    {
        return array_filter($this->fetchAllByUser($profile), static function(Member $member)
        {
            return $member->isContestant;
        });
    }

    /**
     * @param Hour $hour
     * @return Member[]
     */
    public function fetchAllByHour(Hour $hour): array
    {
        $ret = $this->fetchAll(['id IN (SELECT memberId FROM geelhoed_members_hours WHERE hourId = ?)'], [$hour->id]);
        $this->sortByName($ret);

        return $ret;
    }

    /**
     * @param string[] $where
     * @param list<string|int|float|null> $args
     * @param string $afterWhere
     * @return Member[]
     */
    public function fetchAllAndSortByName(array $where = [], array $args = [], string $afterWhere = ''): array
    {
        $list = $this->fetchAll($where, $args, $afterWhere);
        $this->sortByName($list);
        return $list;
    }


    /**
     * @param Member[] $results
     * @return void
     */
    private function sortByName(array &$results): void
    {
        usort($results, Member::compareByName(...));
    }

    public function save(Model $model): void
    {
        $this->genericRepository->save($model);
    }

    private function rebuildHoursCache(): void
    {
        $records = $this->connection->doQueryAndFetchAll('SELECT * FROM geelhoed_members_hours') ?: [];
        foreach ($records as $record)
        {
            $memberId = (int)$record['memberId'];
            if (!array_key_exists($memberId, self::$hoursCache))
            {
                self::$hoursCache[$memberId] = [];
            }

            $hourId = (int)$record['hourId'];
            /** @var Hour $hour */
            $hour = $this->hourRepository->fetchById($hourId);

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
    public function getHours(Member $member): array
    {
        $this->loadHoursCache();

        return self::$hoursCache[$member->id] ?? [];
    }

    /**
     * @param Hour[] $hours
     */
    public function setHours(Member $member, array $hours): void
    {
        $this->loadHoursCache();

        assert(is_int($member->id));
        $this->connection->executeQuery('DELETE FROM geelhoed_members_hours WHERE memberId = ?', [$member->id]);

        if (empty($hours))
        {
            return;
        }

        $sql = 'INSERT INTO geelhoed_members_hours(memberId, hourId) VALUES ';
        foreach ($hours as $hour)
        {
            $sql .= "({$member->id}, {$hour->id}), ";
        }

        $sql = trim($sql, ' ,');
        $this->connection->executeQuery($sql);

        self::$hoursCache[$member->id] = $hours;
        self::$hoursCacheHandle->save(self::$hoursCache);
    }

    /**
     * @throws \Exception
     * @return Sport[]
     */
    public function getSports(Member $member): array
    {
        $sports = [];
        foreach ($this->getHours($member) as $hour)
        {
            $sport = $hour->sport;
            $sports[$sport->id] = $sport;
        }

        return $sports;
    }

    public function getHighestGraduation(Member $member, Sport $sport): Graduation|null
    {
        $graduations = $this->memberGraduationRepository->fetchAllByMember($member);
        // Results are ordered by date, so the reverse it to start with the highest ones.
        foreach (array_reverse($graduations) as $memberGraduation)
        {
            $graduation = $memberGraduation->graduation;
            if ($graduation->sport->id === $sport->id)
            {
                return $graduation;
            }
        }

        return null;
    }

    /**
     * @return Member[]
     */
    public function rebuildMonthlyFeeCacheForMember(Member $member): array
    {
        if (empty(self::$monthlyFeeCacheHandle))
        {
            self::$monthlyFeeCacheHandle = new FileCache('member_monthly_fee_cache', []);
            self::$monthlyFeeCacheHandle->load(self::$monthlyFeeCache);
        }

        if (!empty($member->iban))
        {
            $membersOnSameAccount = $this->fetchAll(['iban = ?'], [$member->iban]);
        }
        else
        {
            $membersOnSameAccount = [$member];
        }

        foreach ($membersOnSameAccount as $member)
        {
            assert(is_int($member->id));
            $fee = $this->getMonthlyFeeUncached($member);
            self::$monthlyFeeCache[$member->id] = $fee;
        }

        self::$monthlyFeeCacheHandle->save(self::$monthlyFeeCache);
        return $membersOnSameAccount;
    }

    private function rebuildMonthlyFeeCache(): void
    {
        if (empty(self::$monthlyFeeCacheHandle))
        {
            self::$monthlyFeeCacheHandle = new FileCache('member_monthly_fee_cache', []);
            self::$monthlyFeeCacheHandle->load(self::$monthlyFeeCache);
        }

        self::$monthlyFeeCache = [];
        $members = $this->fetchAll();
        foreach ($members as $member)
        {
            assert(is_int($member->id));
            $fee = $this->getMonthlyFeeUncached($member);
            self::$monthlyFeeCache[$member->id] = $fee;
        }

        self::$monthlyFeeCacheHandle->save(self::$monthlyFeeCache);
    }

    /**
     * Calculate monthly total from the start of the next quarter,
     * without gezinskorting.
     *
     * @param Member $member
     * @throws \Exception
     * @return float
     */
    public function getMonthlyFeeRaw(Member $member): float
    {
        if ($member->freeParticipation)
        {
            return 0.00;
        }
        if ($member->temporaryStop)
        {
            return 0.00;
        }
        $sports = $this->getSports($member);
        if (count($sports) === 0)
        {
            return 0.00;
        }
        $isSenior = $member->isSenior();
        $discount = $member->discount;
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

    private function getMonthlyFeeUncached(Member $member): float
    {
        if (empty($member->iban))
        {
            return $this->getMonthlyFeeRaw($member);
        }

        $membersOnSameAccount = $this->fetchAll(['iban = ?'], [$member->iban]);

        if (count($membersOnSameAccount) === 1)
        {
            return $this->getMonthlyFeeRaw($member);
        }

        $feesOnThisAccount = [];
        foreach ($membersOnSameAccount as $memberOnAccount)
        {
            $feesOnThisAccount[$memberOnAccount->id] = $this->getMonthlyFeeRaw($memberOnAccount);
        }

        // Sort fees, with the highest coming first.
        uasort($feesOnThisAccount, static function(float $fee1, float $fee2)
        {
            return $fee2 <=> $fee1;
        });

        $currentMemberIndex = 0;
        foreach ($feesOnThisAccount as $memberId => $fee)
        {
            if ($memberId === $member->id)
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
        return $this->getMonthlyFeeRaw($member);
    }

    /**
     * Calculate monthly total from the start of the next quarter.
     *
     * @param Member $member
     * @return float
     */
    public function getMonthlyFee(Member $member): float
    {
        if (empty(self::$monthlyFeeCache))
        {
            self::rebuildMonthlyFeeCache();
        }

        return self::$monthlyFeeCache[$member->id] ?? 0.0;
    }

    /**
     * Calculate quarterly total from the start of the next quarter.
     *
     * @param Member $member
     * @return float
     */
    public function getQuarterlyFee(Member $member): float
    {
        return $this->getMonthlyFee($member) * 3;
    }

}
