<?php
declare(strict_types=1);

namespace Cyndaron\System;

final class ExpectedResult
{
    public function __construct(
        public readonly string $expected,
        public readonly bool|string $result,
    ) {
    }
}
