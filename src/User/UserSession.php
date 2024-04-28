<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use function Safe\session_destroy;
use function session_start;

final class UserSession
{
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

    public static function logout(): void
    {
        session_destroy();
        session_start();
        self::addNotification('U bent afgemeld.');
    }

    public static function getProfile(): User|null
    {
        return $_SESSION['profile'] ?? null;
    }
}
