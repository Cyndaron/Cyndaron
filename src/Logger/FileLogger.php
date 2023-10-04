<?php
declare(strict_types=1);

namespace Cyndaron\Logger;

use DateTimeImmutable;
use Psr\Log\LoggerTrait;
use RuntimeException;
use Stringable;
use function file_put_contents;
use function fwrite;
use function is_dir;
use function is_scalar;
use function mkdir;
use const FILE_APPEND;
use const STDERR;
use function dirname;
use function sprintf;

final class FileLogger implements \Psr\Log\LoggerInterface
{
    use LoggerTrait;

    public function __construct(private readonly string $filename)
    {
        $dirname = dirname($this->filename);
        if (!is_dir($dirname) && !mkdir($dirname, recursive: true))
        {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $dirname));
        }
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $stamp = (new DateTimeImmutable())->format('Y-m-d H:i:s');
        $convertedLevel = (is_scalar($level) || $level instanceof Stringable) ? (string)$level : '???';
        $logline = "[$stamp] [$convertedLevel] $message\n";
        $result = file_put_contents($this->filename, $logline, FILE_APPEND);
        if ($result === false)
        {
            fwrite(STDERR, $logline);
            fwrite(STDERR, "Could not log to {$this->filename}!");
        }
    }
}
