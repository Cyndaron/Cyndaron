<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class CSRFTokenHandler
{
    public function __construct(private readonly SessionInterface $userSession)
    {
    }

    public function get(string $module, string $action): string
    {
        /** @var array<string, array<string, string>>|null $holder */
        $holder = $this->userSession->get('token');
        if (empty($holder))
        {
            $holder = [];
        }
        if (empty($holder[$module]))
        {
            $holder[$module] = [];
        }

        if (empty($holder[$module][$action]))
        {
            $holder[$module][$action] = Util::generateToken(16);
        }

        $this->userSession->set('token', $holder);

        return $holder[$module][$action];
    }

    public function check(string $module, string $action, string $token): bool
    {
        /** @var array<string, array<string, string>>|null $holder */
        $holder = $this->userSession->get('token');
        if (!empty($token) &&
            !empty($holder[$module][$action]) &&
            $token === $holder[$module][$action])
        {
            return true;
        }
        return false;
    }
}
