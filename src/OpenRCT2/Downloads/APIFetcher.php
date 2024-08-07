<?php
declare(strict_types=1);

namespace Cyndaron\OpenRCT2\Downloads;

use Cyndaron\Util\Util;
use function file_exists;
use function time;
use function dirname;

final class APIFetcher
{
    public function fetch(APICall $call): string
    {
        if ($this->hasValidCache($call))
        {
            return \Safe\file_get_contents($call->getCachePath());
        }

        $contents = Util::fetch($call->getUrl());
        $this->saveToCache($call, $contents);
        return $contents;
    }

    private function hasValidCache(APICall $call): bool
    {
        $path = $call->getCachePath();
        if (!file_exists($path))
        {
            return false;
        }

        $validUntil = \Safe\filemtime($path) + $call->getAPITimeout();
        return $validUntil >= time();
    }

    private function saveToCache(APICall $call, string $contents): void
    {
        $path = $call->getCachePath();
        $dir = dirname($path);
        Util::ensureDirectoryExists($dir);
        \Safe\file_put_contents($path, $contents);
    }
}
