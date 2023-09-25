<?php
declare(strict_types=1);

namespace Cyndaron\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Stringable;

final class MultiLogger implements LoggerInterface
{
    use LoggerTrait;

    /** @var LoggerInterface[] */
    private readonly array $loggers;

    /**
     * @param LoggerInterface ...$loggers
     */
    public function __construct(LoggerInterface ...$loggers)
    {
        $this->loggers = $loggers;
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        foreach ($this->loggers as $logger)
        {
            $logger->log($level, $message, $context);
        }
    }
}
