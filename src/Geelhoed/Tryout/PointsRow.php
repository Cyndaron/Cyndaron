<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Tryout;

final class PointsRow
{
    public function __construct(
        public readonly \DateTimeInterface|null $date,
        public readonly int $points,
        public readonly int $accumulativeTotal,
    ) {
    }
}
