<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\DBAL\Model;
use Cyndaron\User\User;
use Cyndaron\Util\Util;

use function Safe\scandir;
use function Safe\strtotime;
use function Safe\substr;
use function assert;
use function file_exists;
use function is_dir;
use function array_filter;
use function reset;
use function count;
use function array_map;
use function implode;
use function time;

final class Contest extends Model
{
    public const TABLE = 'geelhoed_contests';
    public const TABLE_FIELDS = ['name', 'description', 'location', 'sportId', 'registrationDeadline', 'registrationChangeDeadline', 'price'];

    public const RIGHT_MANAGE = 'geelhoed_manage_contests';
    public const RIGHT_PARENT = 'geelhoed_contestant_parent';

    public string $name = '';
    public string $description = '';
    public string $location = '';
    public int $sportId = 0;
    public string $registrationDeadline = '';
    public string $registrationChangeDeadline = '';
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

    public function hasMember(Member $member, bool $includeUnpaid = false): bool
    {
        foreach ($this->getContestMembers($includeUnpaid) as $contestMember)
        {
            if ($contestMember->getMember()->id === $member->id)
            {
                return true;
            }
        }

        return false;
    }

    public function getAttachments(): array
    {
        $folder = Util::UPLOAD_DIR . '/contest/' . $this->id . '/attachments';
        if (!file_exists($folder) || !is_dir($folder))
        {
            return [];
        }
        $files = scandir($folder);
        return array_filter($files, static function($filename)
        {
            // Exclude hidden files.
            return substr($filename, 0, 1) !== '.';
        });
    }

    /**
     * @return ContestDate[]
     */
    public function getDates(): array
    {
        return ContestDate::fetchAll(['contestId = ?'], [$this->id], 'ORDER BY datetime');
    }

    public function getFirstDate(): ?string
    {
        $dates = $this->getDates();
        if (count($dates) === 0)
        {
            return null;
        }

        return reset($dates)->datetime;
    }

    /**
     * @return self[]
     */
    public static function fetchAllCurrentWithDate(): array
    {
        return self::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_dates WHERE datetime > CURRENT_TIMESTAMP)'], [], 'ORDER BY registrationDeadline DESC');
    }

    public static function getTotalDue(User $user): array
    {
        $members = Member::fetchAllByUser($user);
        if (count($members) === 0)
        {
            return [0.00, []];
        }
        $memberIds = array_map(static function(Member $elem)
        {
            return $elem->id;
        }, $members);
        $contests = Contest::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_members WHERE memberId IN (?))'], [implode(',', $memberIds)]);
        $contestMembers = [];
        $due = 0.00;
        foreach ($contests as $contest)
        {
            foreach ($members as $member)
            {
                $contestMember = ContestMember::fetchByContestAndMember($contest, $member);
                if (($contestMember !== null) && !$contestMember->isPaid && time() < strtotime($contest->registrationDeadline))
                {
                    $due += $contest->price;
                    $contestMembers[] = $contestMember;
                }
            }
        }

        return [$due, $contestMembers];
    }

    public function registrationCanBeChanged(User $user): bool
    {
        if ($user->hasRight(self::RIGHT_MANAGE))
        {
            return true;
        }

        $deadline = $this->registrationChangeDeadline;
        if ($deadline === '')
        {
            $deadline = $this->registrationDeadline;
        }

        if ($deadline === '')
        {
            return true;
        }

        return time() <= strtotime($deadline);
    }
}
