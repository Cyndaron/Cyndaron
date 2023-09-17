<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface WithTextPostProcessors
{
    /**
     * @return class-string<TextPostProcessor>[]
     */
    public function getTextPostProcessors(): array;
}
