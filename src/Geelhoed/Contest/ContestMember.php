<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Model;
use Cyndaron\User\User;
use function assert;
use function count;
use function reset;
use function array_map;
use function implode;

final class ContestMember extends Model
{
    public const TABLE = 'geelhoed_contests_members';
    public const TABLE_FIELDS = ['contestId', 'memberId', 'graduationId', 'weight', 'molliePaymentId', 'isPaid', 'comments'];

    public int $contestId;
    public int $memberId;
    public int $graduationId;
    public int $weight;
    public ?string $molliePaymentId = null;
    public bool $isPaid = false;
    public string $comments = '';

    public function getContest(): Contest
    {
        $ret = Contest::loadFromDatabase($this->contestId);
        assert($ret !== null);
        return $ret;
    }

    public function getMember(): Member
    {
        $ret = Member::loadFromDatabase($this->memberId);
        assert($ret !== null);
        return $ret;
    }

    public function getGraduation(): Graduation
    {
        $ret = Graduation::loadFromDatabase($this->graduationId);
        assert($ret !== null);
        return $ret;
    }

    public static function fetchByContestAndMember(Contest $contest, Member $member): ?self
    {
        $results = self::fetchAll(['contestId = ?', 'memberId = ?'], [$contest->id, $member->id]);
        if (count($results) <= 0)
        {
            return null;
        }

        $firstElem = reset($results);
        return $firstElem;
    }

    /**
     * @param Contest $contest
     * @param Member[] $members
     * @return self[]
     */
    public static function fetchAllByContestAndMembers(Contest $contest, array $members): array
    {
        $memberIds = array_map(static function(Member $member) { return $member->id; }, $members);
        return self::fetchAll(['contestId = ?', 'memberId IN (' . implode(',', $memberIds) . ')'], [$contest->id], 'ORDER BY contestId DESC');
    }

    /**
     * @param array $members
     * @return self[]
     */
    public static function fetchAllByMembers(array $members): array
    {
        $memberIds = array_map(static function(Member $member) { return $member->id; }, $members);
        return self::fetchAll(['memberId IN (' . implode(',', $memberIds) . ')'], [], 'ORDER BY contestId DESC');
    }

    /**
     * @param Contest $contest
     * @return self[]
     */
    public static function fetchAllByContest(Contest $contest): array
    {
        return self::fetchAll(['contestId = ?'], [$contest->id]);
    }

    public function canBeChanged(User $user): bool
    {
        $contest = $this->getContest();
        return $contest->registrationCanBeChanged($user);
    }
}
