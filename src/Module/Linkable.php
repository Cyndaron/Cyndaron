<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\DBAL\Connection;
use Cyndaron\Util\Link;

interface Linkable
{
    /**
     * @return Link[]
     */
    public function getList(Connection $connection): array;
}
