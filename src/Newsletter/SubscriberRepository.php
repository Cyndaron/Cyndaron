<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

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

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }
}
