<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Graduation;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Model;

class ContestMember extends Model
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
        return Member::loadFromDatabase($this->memberId);
    }

    public function getGraduation(): Graduation
    {
        return Graduation::loadFromDatabase($this->graduationId);
    }

    public static function fetchByContestAndMember(Contest $contest, Member $member): ?self
    {
        $results = self::fetchAll(['contestId = ?', 'memberId = ?'], [$contest->id, $member->id]);
        if (count($results) <= 0)
        {
            return null;
        }

        return reset($results);
    }
}