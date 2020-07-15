<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\Model;

final class Contest extends Model
{
    public const TABLE = 'geelhoed_contests';
    public const TABLE_FIELDS = ['name', 'description', 'location', 'sportId', 'date', 'registrationDeadline', 'price'];

    public const RIGHT_MANAGE = 'geelhoed_manage_contests';
    public const RIGHT_PARENT = 'geelhoed_contestant_parent';

    public string $name = '';
    public string $description = '';
    public string $location = '';
    public int $sportId = 0;
    public string $date = '';
    public string $registrationDeadline = '';
    public float $price;

    /**
     * @param bool $includeUnpaid
     * @return ContestMember[]
     */
    public function getContestMembers(bool $includeUnpaid = false): array
    {
        $args = ['contestId = ?'];
        if (!$includeUnpaid)
        {
            $args[] = 'isPaid = 1';
        }

        return ContestMember::fetchAll($args, [$this->id]);
    }

    public function getSport(): Sport
    {
        $ret = Sport::loadFromDatabase($this->sportId);
        assert($ret !== null);
        return $ret;
    }

    public function hasMember(Member $member): bool
    {
        foreach ($this->getContestMembers() as $contestMember)
        {
            if ($contestMember->getMember()->id === $member->id)
            {
                return true;
            }
        }

        return false;
    }
}
