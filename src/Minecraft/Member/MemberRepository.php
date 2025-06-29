<?php

declare(strict_types=1);

namespace Cyndaron\Minecraft\Member;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function count;
use function reset;

/**
 * @implements RepositoryInterface<Member>
 */
final class MemberRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Member::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function loadByUsername(string $username): Member|null
    {
        $results = $this->fetchAll(['userName = ?'], [$username]);
        if (count($results) === 0)
        {
            return null;
        }
        return reset($results);
    }
}
