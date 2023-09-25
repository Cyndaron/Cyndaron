<?php
namespace Cyndaron\Mail;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;

final class Mail
{
    public Address|null $replyTo = null;

    public function __construct(
        public Address     $from,
        public Address     $to,
        public string      $subject,
        public string|null $plainTextMessage = null,
        public string|null $htmlMessage = null
    ) {
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
