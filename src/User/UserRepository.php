<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<User>
 */
final class UserRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = User::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function fetchByEmail(string $email): User|null
    {
        return User::fetch(['email = ?'], [$email]);
    }

    public function fetchByUsername(string $username): User|null
    {
        return User::fetch(['username = ?'], [$username]);
    }
}
