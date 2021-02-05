<?php
namespace Cyndaron;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Email;

final class PlainTextMail
{
    public string $to;
    public string $subject;
    public string $message;

    public string $fromAddress;
    public string $fromName;

    public function __construct(string $to, string $subject, string $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;

        $this->fromAddress = Util::getNoreplyAddress();
        $this->fromName = Setting::get('organisation') ?: Setting::get('siteName');
    }

    public function send(): bool
    {
        $transport = new SendmailTransport();
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from("{$this->fromName} <{$this->fromAddress}>")
            ->to($this->to)
            ->subject($this->subject)
            ->text($this->message);

        try
        {
            $mailer->send($email);
            return true;
        }
        catch (\Throwable $e)
        {
            \Safe\error_log((string)$e);
        }

        return false;
    }
}
