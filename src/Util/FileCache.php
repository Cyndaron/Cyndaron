<?php
declare(strict_types=1);

namespace Cyndaron\Util;

use DateTime;
use DateTimeImmutable;
use Throwable;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function serialize;
use function unserialize;
use const CACHE_DIR;
use function fread;
use function filesize;
use function fopen;
use function flock;
use function fwrite;
use function fclose;
use function is_array;

final class FileCache
{
    public const CACHE_DIR = CACHE_DIR . 'cyndaron';

    public readonly string $filename;
    /** @var class-string[]|bool */
    public readonly array|bool $allowedClasses;

    /**
     * @param string $cacheKey
     * @param class-string[] $allowedClasses
     */
    public function __construct(string $cacheKey, array|bool $allowedClasses)
    {
        if (is_array($allowedClasses))
        {
            $allowedClasses[] = DateTime::class;
            $allowedClasses[] = DateTimeImmutable::class;
            $allowedClasses[] = \Safe\DateTime::class;
            $allowedClasses[] = \Safe\DateTimeImmutable::class;
        }

        $this->filename = self::CACHE_DIR . "/$cacheKey.phps";
        $this->allowedClasses = $allowedClasses;
    }

    public function load(mixed &$target): FileCacheLoadResult
    {
        try
        {
            if (!file_exists($this->filename))
            {
                return FileCacheLoadResult::NO_CACHE_FILE_EXISTS;
            }

            $filesize = filesize($this->filename);
            if ($filesize === false || $filesize === 0)
            {
                return FileCacheLoadResult::NO_CACHE_FILE_EXISTS;
            }

            $fp = fopen($this->filename, 'rb');
            if ($fp === false)
            {
                return FileCacheLoadResult::CACHE_FILE_INACCESSIBLE;
            }

            if (!flock($fp, LOCK_SH))
            {
                fclose($fp);
                return FileCacheLoadResult::CACHE_FILE_INACCESSIBLE;
            }

            $serialized = fread($fp, $filesize);
            fclose($fp);
            if (!$serialized)
            {
                return FileCacheLoadResult::CACHE_FILE_BROKEN;
            }

            $unserialized = unserialize($serialized, ['allowed_classes' => $this->allowedClasses]);
            if (!$unserialized)
            {
                return FileCacheLoadResult::CACHE_FILE_BROKEN;
            }

            $target = $unserialized;
            return FileCacheLoadResult::OK;
        }
        catch (Throwable)
        {
            return FileCacheLoadResult::CACHE_FILE_INACCESSIBLE;
        }
    }

    public function save(mixed &$target): void
    {
        Util::ensureDirectoryExists(self::CACHE_DIR);
        $serialized = serialize($target);
        $fp = fopen($this->filename, 'wb');
        if ($fp === false)
        {
            return;
        }

        if (flock($fp, LOCK_EX))
        {
            fwrite($fp, $serialized);
        }
        fclose($fp);
    }
}
