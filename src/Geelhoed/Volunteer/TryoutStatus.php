<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Volunteer;

final class TryoutStatus
{
    public function __construct(
        /** @var array<int, array<string, bool>> */
        public readonly array $fullStatus,
        /** @var array<int, bool> */
        public readonly array $fullRounds,
        /** @var array<string, bool> */
        public readonly array $fullTypes,
    ) {
    }
}
