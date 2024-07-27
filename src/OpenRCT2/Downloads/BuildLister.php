<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Util\Util;
use DateTimeInterface;
use Safe\DateTimeImmutable;
use function usort;

final class BuildLister
{
    public const DEVELOPMENT_BUILDS_URL = 'https://api.github.com/repos/Limetric/OpenRCT2-binaries/releases';
    public const LATEST_DEVELOPMENT_BUILD_URL = 'https://api.github.com/repos/Limetric/OpenRCT2-binaries/releases/latest';
    public const RELEASE_BUILDS_URL = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases';
    public const LATEST_RELEASE_BUILD_URL = 'https://api.github.com/repos/OpenRCT2/OpenRCT2/releases/latest';

    /**
     * @param array{ published_at: string, tag_name: string, assets: list<array{ name: string, size: int, browser_download_url: string }> } $json
     */
    private static function buildJSONToObject(array $json): Build
    {
        $tagName = $json['tag_name'];
        $publishedAt = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $json['published_at']);
        $artifacts = [];
        foreach ($json['assets'] as $asset)
        {
            $artifacts[] = Artifact::fromArray($tagName, $publishedAt, $asset);
        }
        usort($artifacts, function(Artifact $artifact1, Artifact $artifact2)
        {
            return $artifact1->operatingSystem->getPriority() <=> $artifact2->operatingSystem->getPriority();
        });

        return new Build($tagName, $publishedAt, $artifacts);
    }

    /**
     * @return Build[]
     */
    public static function fetchAndProcessBuilds(string $url): array
    {
        $raw = Util::fetch($url);

        /** @var list<array{ published_at: string, tag_name: string, assets: list<array{ name: string, size: int, browser_download_url: string }> }> $buildList */
        $buildList = \Safe\json_decode($raw, true);
        usort($buildList, function(array $build1, array $build2)
        {
            return $build2['published_at'] <=> $build1['published_at'];
        });

        $builds = [];
        foreach ($buildList as $build)
        {
            $builds[] = self::buildJSONToObject($build);
        }

        return $builds;
    }

    public static function fetchAndProcessSingleBuild(string $url): Build
    {
        $raw = Util::fetch($url);
        /** @var array{ published_at: string, tag_name: string, assets: list<array{ name: string, size: int, browser_download_url: string }> } $build */
        $build = \Safe\json_decode($raw, true);
        return self::buildJSONToObject($build);
    }
}
