<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Multiplayer;

final class Server
{
    public function __construct(
        public readonly string $name,
        public readonly string $description,
        public readonly int $monthsElapsed,
        public readonly string $dateFormatted,
        public readonly int $playersCurrently,
        public readonly int $playersMaximum,
        public readonly string $version,
        public readonly bool $requiresPassword,
    ) {
    }
}
