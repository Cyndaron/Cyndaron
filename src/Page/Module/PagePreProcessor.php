<?php
declare(strict_types=1);

namespace Cyndaron\Page\Module;

use Cyndaron\Page\Page;

interface PagePreProcessor
{
    public function process(Page $page): void;
}
