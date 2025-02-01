<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\Util;
use DateTimeInterface;
use DateTime;

use function substr;
use function password_needs_rehash;
use function count;
use function password_verify;
use function password_hash;

final class User extends Model
{
    use FileCachedModel;

    public const TABLE = 'users';

    public const AVATAR_DIR = Util::UPLOAD_DIR . '/user/avatar';

    #[DatabaseField]
    public string $username = '';
    #[DatabaseField]
    public string $password = '';
    #[DatabaseField]
    public string|null $email = null;
    #[DatabaseField]
    public int $level = UserLevel::LOGGED_IN;
    #[DatabaseField]
    public string $firstName = '';
    #[DatabaseField]
    public string $initials = '';
    #[DatabaseField]
    public string $tussenvoegsel = '';
    #[DatabaseField]
    public string $lastName = '';
    #[DatabaseField]
    public string $role = '';
    #[DatabaseField]
    public string $comments = '';
    #[DatabaseField]
    public string $avatar = '';
    #[DatabaseField]
    public bool $hideFromMemberList = false;
    #[DatabaseField]
    public string|null $gender = null;
    #[DatabaseField]
    public string|null $street = null;
    #[DatabaseField]
    public int|null $houseNumber = null;
    #[DatabaseField]
    public string|null $houseNumberAddition = null;
    #[DatabaseField]
    public string|null $postalCode = null;
    #[DatabaseField]
    public string|null $city = null;
    #[DatabaseField]
    public DateTime|null $dateOfBirth = null;
    #[DatabaseField]
    public bool $optOut = false;
    #[DatabaseField]
    public string $notes = '';

    public function isAdmin(): bool
    {
        return $this->level === UserLevel::ADMIN;
    }

    /**
     * @param string $newPassword
     * @return bool
     */
    public function setPassword(string $newPassword): bool
    {
        $result = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->password = $result;
        return true;
    }

    public function passwordIsCorrect(string $password): bool
    {
        return password_verify($password, $this->password);
    }

    public function passwordNeedsUpdate(): bool
    {
        return password_needs_rehash($this->password, PASSWORD_DEFAULT);
    }

    public function getFullName(): string
    {
        $ret = $this->firstName ?: $this->initials;
        $ret .= ' ';
        if ($this->tussenvoegsel !== '')
        {
            $ret .= $this->tussenvoegsel;
            $lastChar = substr($this->tussenvoegsel, -1);
            if ($lastChar !== "'" && $lastChar !== '’')
            {
                $ret .= ' ';
            }
        }

        $ret .= $this->lastName;

        return $ret;
    }

    public function save(): bool
    {
        $userRepository = new UserRepository(new GenericRepository(DBConnection::getPDO()), DBConnection::getPDO());
        $userRepository->generateUsername($this);

        return parent::save();
    }

    public function canLogin(): bool
    {
        return $this->username && $this->password && $this->email;
    }

    public function hasRight(string $right): bool
    {
        if ($this->level === UserLevel::ADMIN)
        {
            return true;
        }

        $records = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM user_rights WHERE `userId` = ? AND `right` = ?', [$this->id, $right]);
        if (count($records) > 0)
        {
            return true;
        }

        return false;
    }

    public function getAge(?DateTimeInterface $on = null): int
    {
        if ($this->dateOfBirth === null)
        {
            return 0;
        }

        if ($on === null)
        {
            $on = new DateTime();
        }
        $interval = $on->diff($this->dateOfBirth);
        return $interval->y;
    }

    public function getAvatarUrl(): string
    {
        if (empty($this->avatar))
        {
            return '';
        }

        $filename = self::AVATAR_DIR . "/{$this->avatar}";
        return Util::filenameToUrl($filename);
    }

    public function getGenderDisplay(): string
    {
        return match ($this->gender)
        {
            'male' => 'm',
            'female' => 'v',
            default => '?',
        };
    }
}
