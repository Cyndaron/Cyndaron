<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\User\User;
use DateTimeInterface;
use function Safe\strtotime;
use function array_map;
use function implode;
use function time;
use function count;
use function reset;

/**
 * @implements RepositoryInterface<Contest>
 */
class ContestRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Contest::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly ContestDateRepository $contestDateRepository,
        private readonly ContestMemberRepository $contestMemberRepository,
    ) {
    }

    /**
     * @return Contest[]
     */
    public function fetchAllCurrentWithDate(): array
    {
        return $this->fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_dates WHERE start > CURRENT_TIMESTAMP)'], [], 'ORDER BY registrationDeadline DESC');
    }

    /**
     * @param User $user
     * @throws \Safe\Exceptions\DatetimeException
     * @return array{0: float, 1: ContestMember[]}
     */
    public function getTotalDue(User $user, MemberRepository $memberRepository): array
    {
        $members = $memberRepository->fetchAllContestantsByUser($user);
        if (count($members) === 0)
        {
            return [0.00, []];
        }
        $memberIds = array_map(static function(Member $elem)
        {
            return $elem->id;
        }, $members);
        $contests = $this->fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (' . implode(',', $memberIds) . '))']);
        $contestMembers = [];
        $due = 0.00;
        foreach ($contests as $contest)
        {
            foreach ($members as $member)
            {
                $contestMember = $this->contestMemberRepository->fetchByContestAndMember($contest, $member);
                if (($contestMember !== null) && !$contestMember->isPaid && time() < strtotime($contest->registrationDeadline))
                {
                    $due += $contest->price;
                    $contestMembers[] = $contestMember;
                }
            }
        }

        return [$due, $contestMembers];
    }

    /**
     * @return ContestDate[]
     */
    public function getDates(Contest $contest): array
    {
        return $this->contestDateRepository->fetchAll(['contestId = ?'], [$contest->id], 'ORDER BY start');
    }

    public function getFirstDate(Contest $contest): DateTimeInterface|null
    {
        $dates = $this->getDates($contest);
        if (count($dates) === 0)
        {
            return null;
        }

        return reset($dates)->start;
    }

    public function hasMember(Contest $contest, Member $member, bool $includeUnpaid = false): bool
    {
        foreach ($this->contestMemberRepository->fetchAllByContest($contest, $includeUnpaid) as $contestMember)
        {
            if ($contestMember->member->id === $member->id)
            {
                return true;
            }
        }

        return false;
    }
}
