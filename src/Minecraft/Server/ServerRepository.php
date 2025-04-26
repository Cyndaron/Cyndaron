<?php

declare(strict_types=1);

namespace Cyndaron\Minecraft\Server;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<Server>
 */
final class ServerRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Server::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
