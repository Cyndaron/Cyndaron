<?php

declare(strict_types=1);

namespace Cyndaron\Mailform;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

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
