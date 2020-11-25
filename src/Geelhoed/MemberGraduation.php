<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Model;
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
        return static::fetchAll(['memberId = ?'], [$member->id], 'ORDER BY date');
    }

    public function getGraduation(): Graduation
    {
        if (!array_key_exists($this->graduationId, static::$graduationCache))
        {
            static::$graduationCache[$this->graduationId] = Graduation::loadFromDatabase($this->graduationId);
        }

        return static::$graduationCache[$this->graduationId];
    }
}
