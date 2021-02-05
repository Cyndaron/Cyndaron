<?php
namespace Cyndaron\Mail;

use Cyndaron\Setting;
use Cyndaron\Util;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use function html_entity_decode;

final class PlainTextMail
{
    public string $subject;
    public string $message;

    public Address $to;
    public Address $from;
    public ?Address $replyTo = null;

    public function __construct(Address $to, string $subject, string $message)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->message = $message;

        $fromName = html_entity_decode(Setting::get('organisation') ?: Setting::get('siteName'));
        $this->from = new Address(Util::getNoreplyAddress(), $fromName);
    }

    public function setFrom(Address $from): void
    {
        $this->from = $from;
    }

    public function addReplyTo(Address $replyToAddress): void
    {
        $this->replyTo = $replyToAddress;
    }

    public function send(): bool
    {
        $transport = new SendmailTransport();
        $mailer = new Mailer($transport);

        $email = (new Email())
            ->from($this->from)
            ->to($this->to)
            ->subject($this->subject)
            ->text($this->message);

        if ($this->replyTo !== null)
        {
            $email->addReplyTo($this->replyTo);
        }

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
