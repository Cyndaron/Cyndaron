<?php
declare(strict_types=1);

namespace Cyndaron\DBAL;

final class DatabaseFieldMapping
{
    public function __construct(
        public readonly string $propertyName,
        public readonly string $dbName,
    ) {
    }
}
