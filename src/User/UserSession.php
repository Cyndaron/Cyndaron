<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Util\Setting;
use function Safe\session_destroy;
use function session_start;

final class UserSession
{
    public static function isAdmin(): bool
    {
        return self::getLevel() === UserLevel::ADMIN;
    }

    public static function isLoggedIn(): bool
    {
        return self::getLevel() > 0;
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
        $profile = self::getProfile();
        if ($profile === null)
        {
            return UserLevel::ANONYMOUS;
        }

        return $profile->level;
    }

    public static function hasSufficientReadLevel(): bool
    {
        $minimumReadLevel = (int)Setting::get('minimumReadLevel');
        return (self::getLevel() >= $minimumReadLevel);
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

    public static function setProfile(User $profile): void
    {
        $_SESSION['profile'] = $profile;
    }

    public static function getRedirect(): string
    {
        return (string)($_SESSION['redirect'] ?? '');
    }

    public static function setRedirect(string|null $url): void
    {
        $_SESSION['redirect'] = $url;
    }

    public static function hasStarted(): bool
    {
        return empty($_SESSION);
    }
}
