<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

final class GitHubAsset
{
    public function __construct(
        public readonly string $name,
        public readonly int $size,
        public readonly string $browserDownloadUrl,
    ) {
    }

    /**
     * @param array{ name?: string, size?: int, browser_download_url?: string } $asset
     * @return self
     */
    public static function fromArray(array $asset): self
    {
        return new self(
            $asset['name'] ?? '',
            (int)($asset['size'] ?? 0),
            $asset['browser_download_url'] ?? ''
        );
    }
}
