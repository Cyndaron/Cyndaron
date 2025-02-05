<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Geelhoed\Member\Member;
use function count;
use function reset;
use function array_map;
use function implode;

/**
 * @implements RepositoryInterface<ContestMember>
 */
final class ContestMemberRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = ContestMember::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function fetchByContestAndMember(Contest $contest, Member $member): ContestMember|null
    {
        $results = $this->fetchAll(['contestId = ?', 'memberId = ?'], [$contest->id, $member->id]);
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
     * @return ContestMember[]
     */
    public function fetchAllByContestAndMembers(Contest $contest, array $members): array
    {
        $memberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $members);
        return $this->fetchAll(['contestId = ?', 'memberId IN (' . implode(',', $memberIds) . ')'], [$contest->id], 'ORDER BY contestId DESC');
    }

    /**
     * @param Member[] $members
     * @return ContestMember[]
     */
    public function fetchAllByMembers(array $members): array
    {
        $memberIds = array_map(static function(Member $member)
        {
            return $member->id;
        }, $members);
        return $this->fetchAll(['memberId IN (' . implode(',', $memberIds) . ')'], [], 'ORDER BY contestId DESC');
    }

    /**
     * @param Contest $contest
     * @param bool $includeUnpaid
     * @return ContestMember[]
     */
    public function fetchAllByContest(Contest $contest, bool $includeUnpaid = false): array
    {
        $args = ['contestId = ?'];
        if (!$includeUnpaid)
        {
            $args[] = 'isPaid = 1';
        }

        return $this->fetchAll($args, [$contest->id]);
    }
}
