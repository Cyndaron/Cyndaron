<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

class UserEntry implements EmailEntry
{
    public function __construct(
        public readonly int $id,
        public readonly int $domainId,
        public readonly string $email,
    ) {
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}
