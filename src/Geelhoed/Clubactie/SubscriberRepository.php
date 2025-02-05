<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Clubactie;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

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
