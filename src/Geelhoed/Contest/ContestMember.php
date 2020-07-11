<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Model;

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
        return $firstElem !== false ? $firstElem : null;
    }

    /**
     * @param Contest $contest
     * @return self[]
     */
    public static function fetchAllByContest(Contest $contest): array
    {
        return self::fetchAll(['contestId = ?'], [$contest->id]);
    }
}
