<?php
namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Member\Member;
use Cyndaron\Geelhoed\Member\MemberRepository;
use Cyndaron\Geelhoed\Sport\Sport;
use Cyndaron\User\User;
use Cyndaron\Util\Util;
use DateTimeInterface;
use function array_filter;
use function array_map;
use function count;
use function file_exists;
use function implode;
use function is_dir;
use function reset;
use function Safe\scandir;
use function Safe\strtotime;
use function substr;
use function time;

final class Contest extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_contests';

    public const RIGHT_MANAGE = 'geelhoed_manage_contests';
    public const RIGHT_PARENT = 'geelhoed_contestant_parent';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public string $description = '';
    #[DatabaseField]
    public string $location = '';
    #[DatabaseField(dbName: 'sportId')]
    public Sport $sport;
    #[DatabaseField]
    public string $registrationDeadline = '';
    #[DatabaseField]
    public string $registrationChangeDeadline = '';
    #[DatabaseField]
    public float $price;





    /**
     * @throws \Safe\Exceptions\DirException
     * @return string[]
     */
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
