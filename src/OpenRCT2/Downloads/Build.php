<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

final class Build
{
    /**
     * @param Artifact[] $artifacts
     */
    public function __construct(
        public readonly string $version,
        public readonly \DateTimeImmutable $publishedAt,
        public readonly array $artifacts,
        public readonly bool $signedWithSignPath,
    ) {
    }
}
