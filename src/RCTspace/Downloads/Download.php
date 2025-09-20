<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace\Downloads;

use DateTime;
use function file_exists;

class Download
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $category,
        public readonly string $submitter,
        public readonly string $submitDate,
        public readonly string $offUrlRoot,
        /**
         * @var string Relative to off URL root
         */
        public readonly string $relativeLocation,
        public readonly string $realFilename,
        public readonly string $mimeType,
    ) {

    }

    public function getPath(): string
    {
        return $this->offUrlRoot . $this->relativeLocation;
    }

    public function presentOnDisk(): bool
    {
        return file_exists($this->getPath());
    }
}
