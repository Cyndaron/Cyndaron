<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\Util\Link;

interface Linkable
{
    /**
     * @return Link[]
     */
    public function getList(): array;
}
