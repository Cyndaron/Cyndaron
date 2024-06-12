<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Util\Setting;
use Symfony\Component\HttpFoundation\Session\FlashBagAwareSessionInterface;

final class UserSession
{
    public function __construct(private readonly FlashBagAwareSessionInterface $session)
    {
    }

    public function isAdmin(): bool
    {
        return $this->getLevel() === UserLevel::ADMIN;
    }

    public function isLoggedIn(): bool
    {
        return $this->getLevel() > 0;
    }

    public function addNotification(string $content, string $type = ''): void
    {
        $this->session->getFlashBag()->add($type, $content);
    }

    /**
     * @return array<string, string[]>
     */
    public function getNotifications(): array
    {
        return $this->session->getFlashBag()->all();
    }

    public function getLevel(): int
    {
        $profile = $this->getProfile();
        if ($profile === null)
        {
            return UserLevel::ANONYMOUS;
        }

        return $profile->level;
    }

    public function hasSufficientReadLevel(): bool
    {
        $minimumReadLevel = (int)Setting::get('minimumReadLevel');
        return ($this->getLevel() >= $minimumReadLevel);
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->addNotification('U bent afgemeld.');
    }

    public function getProfile(): User|null
    {
        /** @var User|null $result */
        $result = $this->session->get('profile');
        return $result;
    }

    public function setProfile(User $profile): void
    {
        $this->session->set('profile', $profile);
    }

    public function getRedirect(): string
    {
        /** @var string|null $result */
        $result = $this->session->get('redirect');
        return (string)$result;
    }

    public function setRedirect(string|null $url): void
    {
        $this->session->set('redirect', $url);
    }

    public function hasStarted(): bool
    {
        return $this->session->isStarted();
    }

    public function start(): bool
    {
        return $this->session->start();
    }

    public function invalidate(): bool
    {
        return $this->session->invalidate();
    }

    public function getSymfonySession(): FlashBagAwareSessionInterface
    {
        return $this->session;
    }
}
