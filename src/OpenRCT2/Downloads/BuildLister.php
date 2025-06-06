<?php
/**
 * Copyright © 2009-2024 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use DateTimeInterface;
use Safe\DateTimeImmutable;
use function usort;
use function str_ends_with;

final class BuildLister
{
    /**
     * @param array{ url: string, published_at: string, tag_name: string, body: string, assets: list<array{ name: string, size: int, browser_download_url: string }> } $json
     */
    private static function buildJSONToObject(array $json, BuildType $buildType): Build
    {
        $tagName = $json['tag_name'];
        $publishedAt = DateTimeImmutable::createFromFormat(DateTimeInterface::ATOM, $json['published_at']);
        $signedBySignPath = $buildType === BuildType::RELEASE && $publishedAt > DateTimeImmutable::createFromFormat('Y-m-d', '2024-12-01');

        $artifacts = [];
        foreach ($json['assets'] as $asset)
        {
            if (str_ends_with($asset['name'], '.txt'))
            {
                continue;
            }

            $gitHubAsset = GitHubAsset::fromArray($asset);

            $artifacts[] = Artifact::fromArray($tagName, $publishedAt, $gitHubAsset, $signedBySignPath);
        }
        usort($artifacts, function(Artifact $artifact1, Artifact $artifact2)
        {
            return $artifact1->operatingSystem->getPriority() <=> $artifact2->operatingSystem->getPriority();
        });

        return new Build($buildType, $tagName, $json['body'], $publishedAt, $artifacts, $signedBySignPath);
    }

    /**
     * @return Build[]
     */
    public static function fetchAndProcessBuilds(APICall $call): array
    {
        $fetcher = new APIFetcher();
        $raw = $fetcher->fetch($call);

        /** @var list<array{ url: string, published_at: string, tag_name: string, body: string, assets: list<array{ name: string, size: int, browser_download_url: string }> }> $buildList */
        $buildList = \Safe\json_decode($raw, true);
        usort($buildList, function(array $build1, array $build2)
        {
            return $build2['published_at'] <=> $build1['published_at'];
        });

        $builds = [];
        foreach ($buildList as $build)
        {
            $builds[] = self::buildJSONToObject($build, $call->getBuildType());
        }

        return $builds;
    }

    public static function fetchAndProcessSingleBuild(APICall $call): Build
    {
        $fetcher = new APIFetcher();
        $raw = $fetcher->fetch($call);
        /** @var array{ url: string, published_at: string, tag_name: string, body: string, assets: list<array{ name: string, size: int, browser_download_url: string }> } $build */
        $build = \Safe\json_decode($raw, true);
        return self::buildJSONToObject($build, $call->getBuildType());
    }
}
