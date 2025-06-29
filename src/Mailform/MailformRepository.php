<?php

declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<Mailform>
 */
final class MailformRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Mailform::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
