<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mailer\Transport\SendmailTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Throwable;
use function Safe\error_log;

final class Sender
{
    private readonly Mailer $mailer;

    public function __construct(
        private readonly AddressHelper $addressHelper,
        private readonly Address $fromAddress,
        private readonly Address $replyToAddress,
        private readonly Address $unsubscribeAddress,
        private readonly NewsletterContents $contents,
    ) {
        $transport = new SendmailTransport();
        $this->mailer = new Mailer($transport);
    }

    public function send(Address $toAddress): bool
    {
        try
        {
            $unsubscribeLink = $this->addressHelper->getUnsubscribeLink($toAddress->getAddress());
            $unsubscribeMessage = '<hr><i>U ontvangt deze e-mail omdat u lid bent of omdat zich heeft ingeschreven voor de nieuwsbrief. <a href="' . $unsubscribeLink . '">Klik hier om u uit te schrijven.</a></i>';

            $email = (new Email())
                ->from($this->fromAddress)
                ->to($toAddress)
                ->subject($this->contents->subject)
                ->addReplyTo($this->replyToAddress)
                ->html($this->contents->body . $unsubscribeMessage);
            foreach ($this->contents->attachments as $attachment)
            {
                $email->attachFromPath($attachment->getPathname(), $attachment->getClientOriginalName(), $attachment->getClientMimeType());
            }
            $email->getHeaders()->addTextHeader('List-Unsubscribe', "<mailto:{$this->unsubscribeAddress->getAddress()}>");
            $this->mailer->send($email);
        }
        catch (Throwable $e)
        {
            error_log((string)$e);
            return false;
        }

        return true;
    }
}
