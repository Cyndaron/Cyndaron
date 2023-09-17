<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface Linkable
{
    /**
     * @return InternalLink[]
     */
    public function getList(): array;
}
