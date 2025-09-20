<?php
declare(strict_types=1);

namespace Cyndaron\RCTspace\RideExchange;

use function file_exists;
use function pathinfo;

final class RideExchangeTrack
{
    public readonly string $calculatedLocation;
    public readonly string $fallbackMessage;
    public readonly string $realFilename;

    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $vehicle,
        public readonly string $category,
        public readonly string $submitter,
        public readonly string $submitDate,
        public readonly string $zipLocation,
        public readonly string $zipLocation2,
        public readonly string $zipLocation3,
        public readonly string $uncompressedLocation,
        public readonly string $uncompressedLocation2,
        string $realFilename,
    ) {
        $calculatedLocation = '';
        $fallbackMessage = '';
        $extension = 'zip';

        if (file_exists($this->zipLocation))
        {
            $calculatedLocation = $this->zipLocation;
        }
        elseif (file_exists($this->zipLocation2))
        {
            $calculatedLocation = $this->zipLocation2;
            $fallbackMessage = '(ZIP not present, falling back to second generation ZIP)';
        }
        elseif (file_exists($this->zipLocation3))
        {
            $calculatedLocation = $this->zipLocation3;
            $fallbackMessage = '(ZIP not present, falling back to first generation ZIP)';
        }
        elseif (file_exists($this->uncompressedLocation))
        {
            $calculatedLocation = $this->uncompressedLocation;
            $extension = pathinfo($this->uncompressedLocation, PATHINFO_EXTENSION);
            $fallbackMessage = '(ZIP not present, falling back to plain track design)';
        }
        elseif (file_exists($this->uncompressedLocation2))
        {
            $calculatedLocation = $this->uncompressedLocation2;
            $extension = pathinfo($this->uncompressedLocation2, PATHINFO_EXTENSION);
            $fallbackMessage = '(ZIP not present, falling back to plain track design)';
        }

        $this->calculatedLocation = $calculatedLocation;
        $this->fallbackMessage = $fallbackMessage;
        $this->realFilename = "{$realFilename}.{$extension}";
    }

    public function getPath(): string
    {
        return $this->calculatedLocation;
    }

    public function presentOnDisk(): bool
    {
        return $this->calculatedLocation !== '';
    }

    public function getMimeType(): string
    {
        $extension = pathinfo($this->calculatedLocation, PATHINFO_EXTENSION);
        return $extension === 'zip' ? 'application/zip' : 'application/octet-stream';
    }
}
