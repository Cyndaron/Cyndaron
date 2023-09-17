<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function serialize;
use function unserialize;

final class FileCache
{
    public const CACHE_DIR = ROOT_DIR . '/cache/cyndaron';

    public readonly string $filename;
    /** @var class-string[] */
    public readonly array $allowedClasses;

    /**
     * @param string $cacheKey
     * @param class-string[] $allowedClasses
     */
    public function __construct(string $cacheKey, array $allowedClasses)
    {
        $this->filename = self::CACHE_DIR . "/$cacheKey.phps";
        $this->allowedClasses = $allowedClasses;
    }

    public function load(mixed &$target): bool
    {
        if (file_exists($this->filename))
        {
            $serialized = file_get_contents($this->filename);
            if ($serialized)
            {
                $unserialized = unserialize($serialized, ['allowed_classes' => $this->allowedClasses]);
                if ($unserialized)
                {
                    $target = $unserialized;
                    return true;
                }
            }
        }

        return false;
    }

    public function save(mixed &$target): void
    {
        Util::ensureDirectoryExists(self::CACHE_DIR);
        file_put_contents($this->filename, serialize($target));
    }
}
