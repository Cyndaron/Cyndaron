<?php
declare(strict_types=1);

namespace Cyndaron\Page\Module;

interface WithPageProcessors
{
    /**
     * @return class-string<PagePreProcessor>[]
     */
    public function getPageprocessors(): array;
}
