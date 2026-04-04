<?php
declare(strict_types=1);

namespace Cyndaron\Feed;

use Cyndaron\Category\Category;
use Cyndaron\Page\Module\PagePreProcessor;
use Cyndaron\Page\Page;
use function sprintf;

final class FeedLinkInjector implements PagePreProcessor
{
    public function process(Page $page): void
    {
        if ($page->template === 'Category/CategoryPage' && $page->model instanceof Category)
        {
            $line = sprintf('<link href="/atom/category/%d" type="application/atom+xml" rel="alternate"/>', $page->model->id);
            $page->addHeadLine($line);
        }
    }
}
