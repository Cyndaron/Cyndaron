<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

class AliasEntry implements EmailEntry
{
    public function __construct(
        public readonly int $id,
        public readonly int $domainId,
        public readonly string $source,
        public readonly string $destination,
    ) {
    }

    public function getEmail(): string
    {
        return $this->source;
    }
}
