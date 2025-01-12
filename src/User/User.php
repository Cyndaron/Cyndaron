<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use DateTimeInterface;
use Exception;
use DateTime;

use Symfony\Component\Mime\Address;
use function sprintf;
use function substr;
use function password_needs_rehash;
use function strtolower;
use function random_int;
use function count;
use function password_verify;
use function password_hash;

final class User extends Model
{
    use FileCachedModel;

    public const TABLE = 'users';
    public const TABLE_FIELDS = ['username', 'password', 'email', 'level', 'firstName', 'initials', 'tussenvoegsel', 'lastName', 'role', 'comments', 'avatar', 'hideFromMemberList', 'gender', 'street', 'houseNumber', 'houseNumberAddition', 'postalCode', 'city', 'dateOfBirth', 'optOut', 'notes'];

    public const AVATAR_DIR = Util::UPLOAD_DIR . '/user/avatar';

    public string $username = '';
    public string $password = '';
    public string|null $email = null;
    public int $level = UserLevel::LOGGED_IN;
    public string $firstName = '';
    public string $initials = '';
    public string $tussenvoegsel = '';
    public string $lastName = '';
    public string $role = '';
    public string $comments = '';
    public string $avatar = '';
    public bool $hideFromMemberList = false;
    public string|null $gender = null;
    public string|null $street = null;
    public int|null $houseNumber = null;
    public string|null $houseNumberAddition = null;
    public string|null $postalCode = null;
    public string|null $city = null;
    public DateTime|null $dateOfBirth = null;
    public bool $optOut = false;
    public string $notes = '';

    public const RESET_PASSWORD_MAIL_TEXT =
        'U vroeg om een nieuw wachtwoord voor %s.

Uw nieuwe wachtwoord is: %s';

    public function isAdmin(): bool
    {
        return $this->level === UserLevel::ADMIN;
    }

    /**
     * @throws \Safe\Exceptions\PasswordException
     * @return string
     */
    public function generatePassword(): string
    {
        $newPassword = Util::generatePassword();
        $this->setPassword($newPassword);
        return $newPassword;
    }

    public function mailNewPassword(string $domain, string $password): bool
    {
        if ($this->email === null)
        {
            throw new Exception('No email address specified!');
        }

        $mail = UtilMail::createMailWithDefaults(
            $domain,
            new Address($this->email),
            'Nieuw wachtwoord ingesteld',
            sprintf(self::RESET_PASSWORD_MAIL_TEXT, Setting::get('siteName'), $password)
        );
        return $mail->send();
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
        if (empty($this->username))
        {
            $initials = $this->initials ?: $this->firstName;
            $username = strtolower("{$initials}{$this->tussenvoegsel}{$this->lastName}");
            // Last resort!
            if ($username === '')
            {
                $username = (string)random_int(10000, 99999);
            }
            else
            {
                $existing = self::fetchAll(['username = ?'], [$username]);
                if (count($existing) > 0)
                {
                    $username .= random_int(10000, 99999);
                }
            }
            $this->username = $username;
        }

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

    public static function fromSession(UserSession $userSession): self|null
    {
        return $userSession->getProfile();
    }

    public function getGenderDisplay(): string
    {
        switch ($this->gender)
        {
            case 'male':
                return 'm';
            case 'female':
                return 'v';
            case null:
            default:
                return '?';
        }
    }

    public function addRight(string $right): bool
    {
        if (empty($this->id))
        {
            throw new \Exception('ID not set!');
        }

        $result = DBConnection::getPDO()->insert('INSERT INTO user_rights(`userId`, `right`) VALUES (?, ?)', [$this->id, $right]);
        return (bool)$result;
    }
}
