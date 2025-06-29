<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<Subscriber>
 */
final class SubscriberRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Subscriber::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }

    public function fetchByHash(string $hash): Subscriber|null
    {
        if ($hash === '')
        {
            return null;
        }
        return $this->fetch(['hash = ?'], [$hash]);
    }
}
