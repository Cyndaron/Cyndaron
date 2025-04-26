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
use Cyndaron\Util\Util;
use Symfony\Component\HttpFoundation\Request;
use function preg_replace_callback;
use function strtolower;
use function str_contains;
use function assert;
use function array_key_exists;
use function implode;

final class DownloadProcessor implements \Cyndaron\Module\TextPostProcessor
{
    public function __construct(private readonly Request $request)
    {
    }

    public function process(string $text): string
    {
        $text = preg_replace_callback('/%newestRelease%/', function()
        {
            return $this->renderBlock(
                APICall::LATEST_RELEASE_BUILD,
                'Download Latest Release',
            );
        }, $text) ?? $text;
        $text = preg_replace_callback('/%specificRelease\|([v0-9\.]+?)%/', function($matches)
        {
            return $this->renderReleaseVersion($matches[1]);
        }, $text) ?? $text;
        $text = preg_replace_callback('/%newestDevelop%/', function()
        {
            return $this->renderBlock(
                APICall::LATEST_DEVELOP_BUILD,
                'Download Development Build',
            );
        }, $text) ?? $text;
        return preg_replace_callback('/%newestLauncher%/', function()
        {
            return $this->renderBlock(
                APICall::LATEST_LAUNCHER_BUILD,
                'Download OpenLauncher',
            );
        }, $text) ?? $text;
    }

    private function renderArtifact(Artifact $artifact, string $title, bool $signedWithSignPath): string
    {
        $informationParts = [
            $artifact->version,
            $artifact->operatingSystem->getFriendlyName(),
            Util::formatSize($artifact->size),
        ];
        if ($signedWithSignPath)
        {
            $informationParts[] = '<a href="/code-signing-policy">Signed</a>';
        }

        $firstLine = '<a href="' . $artifact->downloadLink . '">' . $title . '</a>';
        $secondLine = '<div class="information">' . implode(' — ', $informationParts) . '</div>';

        return $firstLine . $secondLine;
    }

    private function renderBlock(APICall $call, string $title): string
    {
        try
        {
            $build = BuildLister::fetchAndProcessSingleBuild($call);
            $artifact = $this->findBestMatchingArtifact($build);
            return $this->renderArtifact($artifact, $title, $build->signedWithSignPath);
        }
        catch (\Throwable)
        {
            return '';
        }
    }

    private function renderReleaseVersion(string $version): string
    {
        $foundBuild = null;
        $builds = BuildLister::fetchAndProcessBuilds(APICall::RELEASE_BUILDS);
        foreach ($builds as $build)
        {
            if ($build->version === $version)
            {
                $foundBuild = $build;
                break;
            }
        }
        if ($foundBuild === null)
        {
            return '';
        }

        $artifact = $this->findBestMatchingArtifact($foundBuild);
        return $this->renderArtifact($artifact, "Download release", $foundBuild->signedWithSignPath);
    }

    private static function matchByOSArchAndType(Build $build, OperatingSystem $operatingSystem, Architecture $architecture, Type $type): Artifact|null
    {
        $foundMatch = null;
        foreach ($build->artifacts as $artifact)
        {
            if ($artifact->operatingSystem === $operatingSystem
                && $artifact->architecture === $architecture
                && $artifact->type === $type)
            {
                $foundMatch = $artifact;
                break;
            }
        }

        return $foundMatch;
    }

    private static function matchByOSAndArch(Build $build, OperatingSystem $operatingSystem, Architecture $architecture): Artifact|null
    {
        $foundMatch = null;
        foreach ($build->artifacts as $artifact)
        {
            if ($artifact->operatingSystem === $operatingSystem
                && $artifact->architecture === $architecture)
            {
                $foundMatch = $artifact;
                break;
            }
        }

        return $foundMatch;
    }

    private static function matchByOS(Build $build, OperatingSystem $operatingSystem): Artifact|null
    {
        $foundMatch = null;
        foreach ($build->artifacts as $artifact)
        {
            if ($artifact->operatingSystem === $operatingSystem)
            {
                $foundMatch = $artifact;
                break;
            }
        }

        return $foundMatch;
    }

    private function pickFirst(Build $build): Artifact
    {
        assert(array_key_exists(0, $build->artifacts));
        return $build->artifacts[0];
    }

    private function findBestMatchingArtifact(Build $build): Artifact
    {
        $operatingSystem = OperatingSystem::WINDOWS;
        $architecture = Architecture::X86_64;
        $type = Type::PACKAGE;

        $userAgent = strtolower((string)$this->request->headers->get('User-Agent'));
        if (str_contains($userAgent, 'windows'))
        {
            $type = Type::INSTALLER;
            if (str_contains($userAgent, 'arm'))
            {
                $architecture = Architecture::ARM_64;
            }
        }
        elseif (str_contains($userAgent, 'macintosh'))
        {
            $operatingSystem = OperatingSystem::MACOS;
            $architecture = Architecture::UNIVERSAL;
        }
        elseif (str_contains($userAgent, 'linux'))
        {
            $operatingSystem = OperatingSystem::LINUX;
            if (str_contains($userAgent, 'i686'))
            {
                $architecture = Architecture::X86_32;
            }
            elseif (str_contains($userAgent, 'arm'))
            {
                $architecture = Architecture::ARM_64;
            }
        }
        elseif (str_contains($userAgent, 'android'))
        {
            $operatingSystem = OperatingSystem::ANDROID;
            $architecture = Architecture::ARM_64;
        }

        $foundBuild = self::matchByOSArchAndType($build, $operatingSystem, $architecture, $type);
        if ($foundBuild !== null)
        {
            return $foundBuild;
        }

        $foundBuild = self::matchByOSAndArch($build, $operatingSystem, $architecture);
        if ($foundBuild !== null)
        {
            return $foundBuild;
        }

        $foundBuild = self::matchByOS($build, $operatingSystem);
        if ($foundBuild !== null)
        {
            return $foundBuild;
        }

        return self::pickFirst($build);
    }
}
