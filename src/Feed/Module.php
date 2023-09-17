<?php
declare(strict_types=1);

namespace Cyndaron\Feed;

use Cyndaron\Module\Routes;
use Cyndaron\Page\Module\WithPageProcessors;

final class Module implements Routes, WithPageProcessors
{
    public function routes(): array
    {
        return [
            'atom' => AtomController::class,
        ];
    }

    public function getPageprocessors(): array
    {
        return [FeedLinkInjector::class];
    }
}
