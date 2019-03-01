<?php
declare (strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Setting;
use Cyndaron\Util;

class User extends Model
{
    protected static $table = 'gebruikers';

    const RESET_PASSWORD_MAIL_TEXT =
        '<p>U vroeg om een nieuw wachtwoord voor %s.</p>

<p>Uw nieuwe wachtwoord is: %s</p>';

    const MAIL_HEADERS = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: %s <noreply@%s>
EOT;

    public static function isAdmin(): bool
    {
        if (!isset($_SESSION['naam']) || $_SESSION['ip'] != $_SERVER['REMOTE_ADDR'] || $_SESSION['niveau'] < 4)
        {
            return false;
        }
        else
        {
            return true;
        }
    }

    public static function isLoggedIn(): bool
    {
        return (isset($_SESSION['naam']) && $_SESSION['ip'] == $_SERVER['REMOTE_ADDR'] && $_SESSION['niveau'] > 0);
    }

    public static function addNotification(string $content): void
    {
        $_SESSION['meldingen'][] = $content;
    }

    public static function getNotifications(): ?array
    {
        $return = @$_SESSION['meldingen'];
        $_SESSION['meldingen'] = null;
        return $return;
    }

    public static function getLevel(): int
    {
        return intval(@$_SESSION['niveau']);
    }

    public static function hasSufficientReadLevel(): bool
    {
        $minimumReadLevel = intval(Setting::get('minimum_niveau_lezen'));
        return (static::getLevel() >= $minimumReadLevel);
    }

    public function fetchRecord()
    {
        if ($this->id === null)
        {
            throw new \Exception('ID is leeg!');
        }

        $this->record = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM gebruikers WHERE id = ?', [$this->id]);
    }

    public function sendNewPassword()
    {
        if ($this->id === null)
        {
            throw new \Exception('ID is leeg!');
        }

        $newPassword = Util::generatePassword();
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        $pdo = DBConnection::getPdo();
        $prep = $pdo->prepare('UPDATE gebruikers SET wachtwoord=? WHERE id =?');
        $prep->execute([$passwordHash, $this->id]);

        $websiteName = Setting::get('websitenaam');
        $domain = str_replace("www.", "", $_SERVER['HTTP_HOST']);
        $domain = str_replace("http://", "", $domain);
        $domain = str_replace("https://", "", $domain);
        $domain = str_replace("/", "", $domain);

        mail(
            $this->record['email'],
            'Nieuw wachtwoord ingesteld',
            sprintf(self::RESET_PASSWORD_MAIL_TEXT, $websiteName, $newPassword),
            sprintf(self::MAIL_HEADERS, $websiteName, $domain)
        );
    }

    public static function getCSRFToken($module, $action): string
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

    public static function checkToken($module, $action, $token): bool
    {
        if (!empty($token) &&
            !empty($_SESSION['token'][$module][$action]) &&
            $token === $_SESSION['token'][$module][$action])
        {
            return true;
        }
        return false;
    }

    public function save(): bool
    {
        if ($this->id !== null)
        {
            $result = DBConnection::doQuery('UPDATE gebruikers SET gebruikersnaam=?, wachtwoord=?, email=?, niveau=? WHERE id=?', [
                $this->record['gebruikersnaam'], $this->record['wachtwoord'], $this->record['email'], $this->record['niveau'], $this->id
            ]);
            // Above will result either false (failure) or "0" (success)
            return $result === false ? false : true;
        }
        return false;
    }

    public static function create(string $username, string $email, string $password, int $level = UserLevel::LOGGED_IN): ?int
    {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $return = DBConnection::doQuery('INSERT INTO gebruikers(gebruikersnaam, wachtwoord, email, niveau) VALUES (?,?,?,?)', [$username, $passwordHash, $email, $level]);
        return $return ? intval($return) : null;
    }

    public static function login(string $identification, string $password)
    {
        if (strpos($identification, '@') !== false)
        {
            $query = 'SELECT * FROM gebruikers WHERE email=?';
            $updateQuery = 'UPDATE gebruikers SET wachtwoord=? WHERE email=?';
        }
        else
        {
            $query = 'SELECT * FROM gebruikers WHERE gebruikersnaam=?';
            $updateQuery = 'UPDATE gebruikers SET wachtwoord=? WHERE gebruikersnaam=?';
        }

        $userdata = DBConnection::doQueryAndFetchFirstRow($query, [$identification]);

        if (!$userdata)
        {
            throw new IncorrectCredentials('Onbekende gebruikersnaam of e-mailadres.');
        }
        else
        {
            $loginSucceeded = false;
            if (password_verify($password, $userdata['wachtwoord']))
            {
                $loginSucceeded = true;

                if (password_needs_rehash($userdata['wachtwoord'], PASSWORD_DEFAULT))
                {
                    $password = password_hash($password, PASSWORD_DEFAULT);
                    DBConnection::doQuery($updateQuery, [$password, $identification]);
                }
            }

            if ($loginSucceeded)
            {
                $_SESSION['naam'] = $userdata['gebruikersnaam'];
                $_SESSION['email'] = $userdata['email'];
                $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
                $_SESSION['niveau'] = $userdata['niveau'];
                User::addNotification('U bent ingelogd.');
                if ($_SESSION['redirect'])
                {
                    $_SESSION['request'] = $_SESSION['redirect'];
                    $_SESSION['redirect'] = null;
                }
                else
                {
                    $_SESSION['request'] = '/';
                }
                header('Location: ' . $_SESSION['request']);
            }
            else
            {
                throw new IncorrectCredentials('Verkeerd wachtwoord.');
            }
        }
    }

    public static function logout()
    {
        session_start();
        session_destroy();

        session_start();
        User::addNotification('U bent afgemeld.');
        header('Location: /');
    }
}