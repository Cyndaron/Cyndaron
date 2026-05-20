<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface Settings
{
    /**
     * @return Setting[]
     */
    public function settings(): array;
}
