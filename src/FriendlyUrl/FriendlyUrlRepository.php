<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function trim;

/**
 * @implements RepositoryInterface<FriendlyUrl>
 */
final class FriendlyUrlRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = FriendlyUrl::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function fetchByName(string $name): FriendlyUrl|null
    {
        return $this->fetch(['name = ?'], [trim($name, '/')]);
    }

    public function fetchByTarget(string $target): FriendlyUrl|null
    {
        return $this->fetch(['target = ?'], [$target]);
    }
}
