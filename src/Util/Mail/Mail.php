<?php
namespace Cyndaron\Util\Mail;

use Cyndaron\Util\Setting;
use Cyndaron\Util\Util;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;
use function html_entity_decode;

final class Mail
{
    public string $subject;
    public ?string $plainTextMessage;
    public ?string $htmlMessage;

    public Address $to;
    public Address $from;
    public ?Address $replyTo = null;

    public function __construct(Address $to, string $subject, ?string $plainTextMessage = null, ?string $htmlMessage = null)
    {
        $this->to = $to;
        $this->subject = $subject;
        $this->plainTextMessage = $plainTextMessage;
        $this->htmlMessage = $htmlMessage;

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
            ->subject($this->subject);

        if ($this->replyTo !== null)
        {
            $email->addReplyTo($this->replyTo);
        }

        if ($this->plainTextMessage)
        {
            $email->text($this->plainTextMessage);
        }
        if ($this->htmlMessage)
        {
            $email->html($this->htmlMessage);
        }

        try
        {
            $mailer->send($email);
            return true;
        }
        catch (Throwable $e)
        {
            \Safe\error_log((string)$e);
        }

        return false;
    }
}
