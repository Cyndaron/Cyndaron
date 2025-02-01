<?php
declare(strict_types=1);

namespace Cyndaron\FriendlyUrl;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;
use function ltrim;

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
        return FriendlyUrl::fetch(['name = ?'], [ltrim($name, '/')]);
    }
}
