<?php
declare(strict_types=1);

namespace Cyndaron\LDBF;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<Request>
 */
final class RequestRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Request::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
