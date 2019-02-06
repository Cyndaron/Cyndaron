<?php
declare(strict_types = 1);

namespace Cyndaron\User;

use Cyndaron\DBConnection;
use Cyndaron\Setting;
use Cyndaron\Util;

class User
{
    public $id = null;
    public $record = null;

    const MAIL_TEXT =
        '<p>U vroeg om een nieuw wachtwoord voor %s.</p>

<p>Uw nieuwe wachtwoord is: %s</p>';

    const MAIL_HEADERS = <<<EOT
MIME-Version: 1.0
Content-type: text/html; charset=utf-8
From: %s <noreply@%s>
EOT;

    public function __construct(?int $id)
    {
        $this->id = $id;
    }

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

        $this->record = DBConnection::getInstance()->doQueryAndFetchFirstRow('SELECT * FROM gebruikers WHERE id = ?', [$this->id]);
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
            sprintf(self::MAIL_TEXT, $websiteName, $newPassword),
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

    public static function checkTokenOrDie($module, $action, $token)
    {

    }
}