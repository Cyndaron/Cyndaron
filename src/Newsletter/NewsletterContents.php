<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class NewsletterContents
{
    /**
     * @param string $subject
     * @param string $body
     * @param UploadedFile[] $attachments
     */
    public function __construct(
        public readonly string $subject,
        public readonly string $body,
        public readonly array $attachments = [],
    ) {
    }
}
