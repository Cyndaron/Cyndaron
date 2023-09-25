<?php
declare(strict_types=1);

namespace Cyndaron\Logger;

use DateTimeImmutable;
use Illuminate\Support\Stringable;
use Psr\Log\LoggerTrait;
use function file_put_contents;
use function fwrite;
use function is_scalar;
use const FILE_APPEND;
use const STDERR;

final class FileLogger implements \Psr\Log\LoggerInterface
{
    use LoggerTrait;

    public function __construct(private readonly string $filename)
    {
    }

    public function log($level, \Stringable|string $message, array $context = [])
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
