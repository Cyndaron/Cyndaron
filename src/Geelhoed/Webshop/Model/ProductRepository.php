<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;

/**
 * @implements RepositoryInterface<Product>
 */
final class ProductRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Product::class;

    use RepositoryTrait;

    public function __construct(private readonly GenericRepository $genericRepository)
    {
    }
}
