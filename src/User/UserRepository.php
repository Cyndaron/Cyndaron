<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\DBAL\Connection;
use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function count;
use function random_int;
use function strtolower;

/**
 * @implements RepositoryInterface<User>
 */
final class UserRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = User::class;

    use RepositoryTrait { save as public traitSave; }

    public function __construct(
        private readonly GenericRepository $genericRepository,
        private readonly Connection $connection
    ) {
    }

    public function fetchByEmail(string $email): User|null
    {
        return $this->fetch(['email = ?'], [$email]);
    }

    public function fetchByUsername(string $username): User|null
    {
        return $this->fetch(['username = ?'], [$username]);
    }

    public function generateUsername(User $user): void
    {
        if (!empty($user->username))
        {
            return;
        }

        $initials = $user->initials ?: $user->firstName;
        $username = strtolower("{$initials}{$user->tussenvoegsel}{$user->lastName}");
        /** @var string $username */
        $username = \Safe\preg_replace('/[^a-z]/', '', $username);
        // Last resort!
        if ($username === '')
        {
            $username = (string)random_int(10000, 99999);
        }
        else
        {
            $existing = $this->fetchAll(['username = ?'], [$username]);
            if (count($existing) > 0)
            {
                $username .= random_int(10000, 99999);
            }
        }
        $user->username = $username;
    }

    public function addRightToUser(User $user, string $right): bool
    {
        if (empty($user->id))
        {
            throw new \Exception('ID not set!');
        }

        $result = $this->connection->insert('INSERT INTO user_rights(`userId`, `right`) VALUES (?, ?)', [$user->id, $right]);
        return (bool)$result;
    }

    public function userHasRight(User $user, string $right): bool
    {
        if ($user->level === UserLevel::ADMIN)
        {
            return true;
        }

        $records = $this->connection->doQueryAndFetchAll('SELECT * FROM user_rights WHERE `userId` = ? AND `right` = ?', [$user->id, $right]);
        if (count($records) > 0)
        {
            return true;
        }

        return false;
    }

    public function save(Model $model): void
    {
        $this->generateUsername($model);
        $this->traitSave($model);
    }
}
