<?php
declare(strict_types=1);

namespace Cyndaron\Base;

final class CyndaronConfig
{
    public function __construct(
        public readonly string $databaseHost,
        public readonly string $databaseUser,
        public readonly string $databasePassword,
        public readonly string $databaseName,
        /** @var array<class-string> */
        public readonly array $enabledModules,
    ) {
    }
}
