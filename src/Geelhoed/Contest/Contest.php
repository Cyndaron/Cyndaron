<?php
namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Sport;
use Cyndaron\Model;
use Cyndaron\Util;

use function Safe\scandir;
use function Safe\substr;

final class Contest extends Model
{
    public const TABLE = 'geelhoed_contests';
    public const TABLE_FIELDS = ['name', 'description', 'location', 'sportId', 'registrationDeadline', 'price'];

    public const RIGHT_MANAGE = 'geelhoed_manage_contests';
    public const RIGHT_PARENT = 'geelhoed_contestant_parent';

    public string $name = '';
    public string $description = '';
    public string $location = '';
    public int $sportId = 0;
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

        return $dates[0]->datetime;
    }

    /**
     * @return self[]
     */
    public static function fetchAllCurrentWithDate(): array
    {
        return self::fetchAll(['id IN (SELECT contestId FROM geelhoed_contests_dates WHERE datetime > CURRENT_TIMESTAMP)'], [], 'ORDER BY registrationDeadline DESC');
    }
}
