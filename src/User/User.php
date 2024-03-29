<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\User\Module\UserMenuItem;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use DateTimeInterface;
use Exception;
use finfo;
use DateTime;
use Safe\Exceptions\FilesystemException;
use Safe\Exceptions\ImageException;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Address;
use function Safe\file_get_contents;
use function Safe\imagecreatefromgif;
use function Safe\imagecreatefromjpeg;
use function Safe\imagecreatefrompng;
use function Safe\imagepng;
use function Safe\session_destroy;
use function sprintf;
use function substr;
use function Safe\unlink;
use function file_exists;
use function basename;
use function password_needs_rehash;
use function session_start;
use function strtolower;
use function random_int;
use function count;
use function array_filter;
use function password_verify;
use function password_hash;
use function str_contains;

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

    public static function isAdmin(): bool
    {
        return isset($_SESSION['username']) && $_SESSION['level'] >= 4;
    }

    public static function isLoggedIn(): bool
    {
        return (isset($_SESSION['username']) && $_SESSION['level'] > 0);
    }

    public static function addNotification(string $content): void
    {
        $_SESSION['notifications'][] = $content;
    }

    /**
     * @deprecated
     * @return string[]|null
     */
    public static function getNotifications(): array|null
    {
        $return = $_SESSION['notifications'] ?? null;
        $_SESSION['notifications'] = null;
        return $return;
    }

    public static function getLevel(): int
    {
        return isset($_SESSION['level']) ? (int)$_SESSION['level'] : UserLevel::ANONYMOUS;
    }

    public static function hasSufficientReadLevel(): bool
    {
        $minimumReadLevel = (int)Setting::get('minimumReadLevel');
        return (self::getLevel() >= $minimumReadLevel);
    }

    /**
     * @throws \Safe\Exceptions\PasswordException
     * @return string
     */
    public function generatePassword(): string
    {
        $newPassword = Util::generatePassword();
        $hashResult = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->password = $hashResult;
        return $newPassword;
    }

    public function resetPassword(): void
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is leeg!');
        }

        $newPassword = $this->generatePassword();

        $pdo = DBConnection::getPDO();
        $prep = $pdo->prepare('UPDATE users SET password=? WHERE id =?');
        $prep->execute([$this->password, $this->id]);

        $this->mailNewPassword($newPassword);
    }

    public function uploadNewAvatar(Request $request): void
    {
        Util::ensureDirectoryExists(self::AVATAR_DIR);
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file */
        $file = $request->files->get('avatarFile');
        $tmpName = $file->getPathname();
        try
        {
            $buffer = file_get_contents($tmpName);
        }
        catch (FilesystemException $e)
        {
            throw new Exception('Kon de inhoud van de avatar niet lezen!');
        }

        try
        {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($buffer);
            switch ($mimeType)
            {
                case 'image/gif':
                    $avatarImg = imagecreatefromgif($tmpName);
                    break;
                case 'image/jpeg':
                    $avatarImg = imagecreatefromjpeg($tmpName);
                    break;
                case 'image/png':
                    $avatarImg = imagecreatefrompng($tmpName);
                    break;
                default:
                    throw new Exception('Ongeldig bestandstype');
            }
        }
        catch (ImageException $e)
        {
            throw new Exception('Kon de bestandsinhoud niet verwerken!');
        }

        $filename = self::AVATAR_DIR . "/{$this->id}.png";
        if (file_exists($filename))
        {
            unlink($filename);
        }

        imagepng($avatarImg, $filename);
        unlink($tmpName);

        $this->avatar = basename($filename);
        $this->save();
    }

    public function mailNewPassword(string $password): bool
    {
        if ($this->email === null)
        {
            throw new Exception('No email address specified!');
        }

        $mail = UtilMail::createMailWithDefaults(
            new Address($this->email),
            'Nieuw wachtwoord ingesteld',
            sprintf(self::RESET_PASSWORD_MAIL_TEXT, Setting::get('siteName'), $password)
        );
        return $mail->send();
    }

    public static function getCSRFToken(string $module, string $action): string
    {
        if (empty($_SESSION['token']))
        {
            $_SESSION['token'] = [];
        }
        if (empty($_SESSION['token'][$module]))
        {
            $_SESSION['token'][$module] = [];
        }

        if (empty($_SESSION['token'][$module][$action]))
        {
            $_SESSION['token'][$module][$action] = Util::generateToken(16);
        }

        return $_SESSION['token'][$module][$action];
    }

    public static function checkToken(string $module, string $action, string $token): bool
    {
        if (!empty($token) &&
            !empty($_SESSION['token'][$module][$action]) &&
            $token === $_SESSION['token'][$module][$action])
        {
            return true;
        }
        return false;
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

    public static function login(string $identification, string $password): string
    {
        if (str_contains($identification, '@'))
        {
            $user = self::fetch(['email = ?'], [$identification]);
            $updateQuery = 'UPDATE users SET password=? WHERE email=?';
        }
        else
        {
            $user = self::fetch(['username = ?'], [$identification]);
            $updateQuery = 'UPDATE users SET password=? WHERE username=?';
        }

        if ($user === null)
        {
            throw new IncorrectCredentials('Onbekende gebruikersnaam of e-mailadres.');
        }

        $loginSucceeded = false;
        if (password_verify($password, $user->password))
        {
            $loginSucceeded = true;

            if (password_needs_rehash($user->password, PASSWORD_DEFAULT))
            {
                $password = password_hash($password, PASSWORD_DEFAULT);
                DBConnection::getPDO()->executeQuery($updateQuery, [$password, $identification]);
            }
        }

        if (!$loginSucceeded)
        {
            throw new IncorrectCredentials('Verkeerd wachtwoord.');
        }

        $_SESSION['userId'] = $user->id;
        $_SESSION['profile'] = $user;
        $_SESSION['username'] = $user->username;
        $_SESSION['email'] = $user->email;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
        $_SESSION['level'] = $user->level;

        self::addNotification('U bent ingelogd.');

        if ($_SESSION['redirect'])
        {
            $redirect = $_SESSION['redirect'];
            $_SESSION['redirect'] = null;
        }
        else
        {
            $redirect = '/';
        }

        return $redirect;
    }

    public static function logout(): void
    {
        session_destroy();
        session_start();
        self::addNotification('U bent afgemeld.');
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
        if ($records !== false && count($records) > 0)
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

    public static function fromSession(): self|null
    {
        return $_SESSION['profile'] ?? null;
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

    public function checkPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
}
