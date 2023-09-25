<?php
declare(strict_types=1);

namespace Cyndaron\Mail;

use Cyndaron\Util\Setting;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use Stringable;
use Symfony\Component\Mime\Address;

final class MailLogger implements LoggerInterface
{
    use LoggerTrait;

    public function __construct(private readonly Address $recipient)
    {
    }

    private function shouldLog(mixed $level): bool
    {
        switch ($level)
        {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                return true;
        }

        return false;
    }

    public function log(mixed $level, Stringable|string $message, array $context = []): void
    {
        if (!$this->shouldLog($level)) {
            return;
        }

        $sitename = Setting::get('siteName');
        $mail = new Mail($this->recipient, "Fout in {$sitename}", (string)$message);
        $mail->send();
    }
}
