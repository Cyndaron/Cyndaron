<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\DBAL\Repository\RepositoryInterface;
use Cyndaron\DBAL\Repository\RepositoryTrait;
use function array_filter;

/**
 * @implements RepositoryInterface<Reply>
 */
final class ReplyRepository implements RepositoryInterface
{
    private const UNDERLYING_CLASS = Reply::class;

    use RepositoryTrait;

    public function __construct(
        private readonly GenericRepository $genericRepository,
    ) {
    }

    /**
     * @param StaticPageModel $staticPage
     * @return Reply[]
     */
    public function fetchByStaticPage(StaticPageModel $staticPage): array
    {
        return array_filter($this->fetchAll(), fn (Reply $reply) => $reply->sub->id === $staticPage->id);
    }
}
