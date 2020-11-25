<?php
namespace Cyndaron;

use function Safe\sprintf;
use function mail;

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
        $additionalHeaders = [
            'From' => sprintf('%s <%s>', $this->fromName, $this->fromAddress),
            'Content-Type' => 'text/plain; charset="UTF-8"',
        ];
        // Set the envelope sender. This is often needed to make DMARC checks pass if multiple domains send mail from the same server.
        $additionalParameters = "-f{$this->fromAddress}";

        return mail($this->to, $this->subject, $this->message, $additionalHeaders, $additionalParameters);
    }
}
