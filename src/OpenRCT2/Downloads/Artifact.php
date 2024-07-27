<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\OpenRCT2\Downloads\Classification\Architecture;
use Cyndaron\OpenRCT2\Downloads\Classification\OperatingSystem;
use Cyndaron\OpenRCT2\Downloads\Classification\Type;
use DateTimeInterface;
use function str_contains;
use function strtolower;

final class Artifact
{
    public function __construct(
        public readonly string $version,
        public readonly DateTimeInterface $publishedAt,
        public readonly OperatingSystem $operatingSystem,
        public readonly Architecture $architecture,
        public readonly Type $type,
        public readonly int $size,
        public readonly string $downloadLink,
        public readonly bool $inDefaultSelection,
    ) {
    }

    /**
     * @param string $tagName
     * @param DateTimeInterface $publishedAt
     * @param array{ name?: string, size?: int, browser_download_url?: string } $asset
     * @return self
     */
    public static function fromArray(string $tagName, DateTimeInterface $publishedAt, array $asset): self
    {
        $version = $tagName;
        $assetName = strtolower($asset['name'] ?? '');

        $operatingSystem = OperatingSystem::OTHER;
        $architecture = Architecture::OTHER;
        $type = Type::PACKAGE;
        $size = $asset['size'] ?? 0;
        $inDefaultSelection = false;

        if (str_contains($assetName, 'android'))
        {
            $operatingSystem = OperatingSystem::ANDROID;
            if (str_contains($assetName, '-arm'))
            {
                $architecture = Architecture::ARM_64;
                $inDefaultSelection = true;
            }
        }
        elseif (str_contains($assetName, 'linux'))
        {
            $operatingSystem = OperatingSystem::LINUX;
            if (str_contains($assetName, 'appimage'))
            {
                $inDefaultSelection = true;
            }
            else
            {
                $type = Type::PORTABLE;
            }

            if (str_contains($assetName, 'i686'))
            {
                $architecture = Architecture::X86_32;
            }
            elseif (str_contains($assetName, 'aarch64'))
            {
                $architecture = Architecture::ARM_64;
            }
            else
            {
                $architecture = Architecture::X86_64;
            }
        }
        elseif (str_contains($assetName, 'macos'))
        {
            $operatingSystem = OperatingSystem::MACOS;
            if (str_contains($assetName, '-universal'))
            {
                $architecture = Architecture::UNIVERSAL;
            }
            elseif (str_contains($assetName, '-x86-64'))
            {
                $architecture = Architecture::X86_64;
            }
            $inDefaultSelection = true;
        }
        elseif (str_contains($assetName, 'windows'))
        {
            $operatingSystem = OperatingSystem::WINDOWS;

            if (str_contains($assetName, 'win32'))
            {
                $architecture = Architecture::X86_32;
            }
            elseif (str_contains($assetName, 'x64'))
            {
                $architecture = Architecture::X86_64;
            }
            elseif (str_contains($assetName, 'arm64'))
            {
                $architecture = Architecture::ARM_64;
            }

            if (str_contains($assetName, '-installer'))
            {
                $type = Type::INSTALLER;
                if ($architecture === Architecture::X86_64)
                {
                    $inDefaultSelection = true;
                }
            }
            elseif (str_contains($assetName, '-symbols'))
            {
                $type = Type::SYMBOLS;
            }
            elseif (str_contains($assetName, '-portable'))
            {
                $type = Type::PORTABLE;
            }
        }

        $downloadLink = $asset['browser_download_url'] ?? '';

        return new self($version, $publishedAt, $operatingSystem, $architecture, $type, $size, $downloadLink, $inDefaultSelection);
    }
}
