<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\View\Page;

interface PagePreProcessor
{
    public function process(Page $page): void;
}
