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
use function explode;
use function count;

final class Artifact
{
    public function __construct(
        public readonly string $version,
        public readonly DateTimeInterface $publishedAt,
        public readonly OperatingSystem $operatingSystem,
        public readonly Architecture $architecture,
        public readonly string $operatingSystemVersion,
        public readonly Type $type,
        public readonly int $size,
        public readonly string $downloadLink,
        public readonly bool $inDefaultSelection,
        public readonly bool $signedWithSignPath,
    ) {
    }

    public static function fromArray(string $tagName, DateTimeInterface $publishedAt, GitHubAsset $asset, bool $signedBySignPath): self
    {
        $version = $tagName;
        $assetName = strtolower($asset->name);

        $operatingSystem = OperatingSystem::OTHER;
        $architecture = Architecture::OTHER;
        $osVersion = '';
        $type = Type::PACKAGE;
        $inDefaultSelection = false;

        if ($assetName === 'openlauncher')
        {
            $operatingSystem = OperatingSystem::LINUX;
            $architecture = Architecture::X86_64;
            $type = Type::PORTABLE;
            $inDefaultSelection = true;
        }
        elseif ($assetName === 'openlauncher.exe' || $assetName === 'openlauncher-win-x64.exe')
        {
            $operatingSystem = OperatingSystem::WINDOWS;
            $architecture = Architecture::X86_64;
            $type = Type::PORTABLE;
            $inDefaultSelection = true;
        }
        elseif (str_contains($assetName, 'android'))
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
            return self::processLinuxArtifact($asset, $version, $publishedAt, $signedBySignPath);
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
            return self::processWindowsArtifact($asset, $version, $publishedAt, $signedBySignPath);
        }

        return new self($version, $publishedAt, $operatingSystem, $architecture, $osVersion, $type, $asset->size, $asset->browserDownloadUrl, $inDefaultSelection, $signedBySignPath);
    }

    private static function processWindowsArtifact(GitHubAsset $asset, string $version, DateTimeInterface $publishedAt, bool $signedBySignPath): self
    {
        $architecture = Architecture::OTHER;
        $inDefaultSelection = false;
        $type = Type::PACKAGE;

        $assetName = strtolower($asset->name);
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

        return new self(
            $version,
            $publishedAt,
            OperatingSystem::WINDOWS,
            $architecture,
            '',
            $type,
            $asset->size,
            $asset->browserDownloadUrl,
            $inDefaultSelection,
            $signedBySignPath
        );
    }

    private static function processLinuxArtifact(GitHubAsset $asset, string $version, DateTimeInterface $publishedAt, bool $signedBySignPath): self
    {
        $type = Type::PORTABLE;
        $inDefaultSelection = false;
        $osVersion = '';
        $assetName = strtolower($asset->name);
        if (str_contains($assetName, 'appimage'))
        {
            $type = Type::PACKAGE;
            $inDefaultSelection = true;
        }
        else
        {
            $assetNameParts = explode('-', $assetName);
            $numParts = count($assetNameParts);
            if ($numParts >= 5)
            {
                $osVersion = $assetNameParts[$numParts - 2];
            }
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

        return new self(
            $version,
            $publishedAt,
            OperatingSystem::LINUX,
            $architecture,
            $osVersion,
            $type,
            $asset->size,
            $asset->browserDownloadUrl,
            $inDefaultSelection,
            $signedBySignPath
        );
    }
}
