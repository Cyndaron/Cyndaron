<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\DBAL\Model;
use function array_key_exists;

final class MemberGraduation extends Model
{
    public const TABLE = 'geelhoed_members_graduations';
    public const TABLE_FIELDS = ['memberId', 'graduationId', 'date'];

    public int $memberId;
    public int $graduationId;
    public string $date;

    private static array $graduationCache = [];

    /**
     * @param Member $member
     * @return static[]
     */
    public static function fetchAllByMember(Member $member): array
    {
        return self::fetchAll(['memberId = ?'], [$member->id], 'ORDER BY date');
    }

    public function getGraduation(): Graduation
    {
        if (!array_key_exists($this->graduationId, self::$graduationCache))
        {
            self::$graduationCache[$this->graduationId] = Graduation::fetchById($this->graduationId);
        }

        return self::$graduationCache[$this->graduationId];
    }
}
