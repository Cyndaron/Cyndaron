<?php
declare(strict_types=1);

namespace Cyndaron\Feed;

use Cyndaron\Category\Category;
use Cyndaron\Category\CategoryIndexPage;
use Cyndaron\Module\PagePreProcessor;
use Cyndaron\Page\Page;
use function sprintf;

final class FeedLinkInjector implements PagePreProcessor
{
    public function process(Page $page): void
    {
        if ($page instanceof CategoryIndexPage)
        {
            /** @var Category $category */
            $category = $page->getTemplateVar('model');
            $line = sprintf('<link href="/atom/category/%d" type="application/atom+xml" rel="alternate"/>', $category->id);
            $page->addHeadLine($line);
        }
    }
}
