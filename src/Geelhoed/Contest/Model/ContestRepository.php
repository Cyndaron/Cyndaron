<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\User\User;
use Cyndaron\User\UserRepository;
use Safe\Exceptions\DatetimeException;
use function array_map;
use function count;
use function implode;
use function Safe\strtotime;
use function time;

/**
 * @implements RepositoryInterface<Contest>
 */
class ContestRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Contest::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly ContestMemberRepository $contestMemberRepository,
        private readonly UserRepository $userRepository,
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
     * @param MemberRepository $memberRepository
     * @throws DatetimeException
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

    public function registrationCanBeChanged(Contest $contest, User $user): bool
    {
        if ($this->userRepository->userHasRight($user, Contest::RIGHT_MANAGE))
        {
            return true;
        }

        $deadline = $contest->registrationChangeDeadline;
        if ($deadline === '')
        {
            $deadline = $contest->registrationDeadline;
        }

        if ($deadline === '')
        {
            return true;
        }

        return time() <= strtotime($deadline);
    }
}
