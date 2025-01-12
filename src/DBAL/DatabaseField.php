<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

use Attribute;

#[Attribute]
class DatabaseField
{
    public function __construct(
        public string $dbName = ''
    ) {
    }
}
