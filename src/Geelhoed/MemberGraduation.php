<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\FileCache;
use function array_key_exists;
use function assert;

final class MemberGraduation extends Model
{
    public const TABLE = 'geelhoed_members_graduations';

    #[DatabaseField]
    public int $memberId;
    #[DatabaseField]
    public int $graduationId;
    #[DatabaseField]
    public string $date;

    /** @var array<int, Graduation> */
    private static array $graduationCache = [];

    /** @var array<int, MemberGraduation[]> */
    private static array $byMemberCache = [];
    private static FileCache $byMemberCacheHandle;

    public static function rebuildByMemberCache(): void
    {
        if (empty(self::$byMemberCacheHandle))
        {
            self::$byMemberCacheHandle = new FileCache('member_graduation_by_member', [self::class]);
            self::$byMemberCacheHandle->load(self::$byMemberCache);
        }

        self::$byMemberCache = [];

        $memberGraduations = self::fetchAll([], [], 'ORDER BY date');
        foreach ($memberGraduations as $memberGraduation)
        {
            $memberId = $memberGraduation->memberId;
            if (!array_key_exists($memberId, self::$byMemberCache))
            {
                self::$byMemberCache[$memberId] = [];
            }

            self::$byMemberCache[$memberId][] = $memberGraduation;
        }

        self::$byMemberCacheHandle->save(self::$byMemberCache);
    }

    /**
     * @param Member $member
     * @return MemberGraduation[]
     */
    public static function fetchAllByMember(Member $member): array
    {
        if (empty(self::$byMemberCache))
        {
            self::rebuildByMemberCache();
        }

        return self::$byMemberCache[$member->id] ?? [];
    }

    private function getGraduationUncached(): Graduation
    {
        $ret = Graduation::fetchById($this->graduationId);
        assert($ret !== null);
        return $ret;
    }

    public function getGraduation(): Graduation
    {
        if (!array_key_exists($this->graduationId, self::$graduationCache))
        {
            self::$graduationCache[$this->graduationId] = $this->getGraduationUncached();
        }

        return self::$graduationCache[$this->graduationId];
    }

    public function save(): bool
    {
        $result = parent::save();
        if ($result)
        {
            self::rebuildByMemberCache();
        }

        return $result;
    }
}
