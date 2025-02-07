<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

use Cyndaron\DBAL\GenericRepository;
use Cyndaron\DBAL\RepositoryInterface;
use Cyndaron\DBAL\RepositoryTrait;

/**
 * @implements RepositoryInterface<PhotoalbumCaption>
 */
final class PhotoalbumCaptionRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = PhotoalbumCaption::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    public function fetchByHash(string $hash): PhotoalbumCaption|null
    {
        return $this->fetch(['hash = ?'], [$hash]);
    }
}
