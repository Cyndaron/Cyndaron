<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest\Model;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<ContestClass>
 */
class ContestClassRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = ContestClass::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
