<?php
declare(strict_types=1);

namespace Cyndaron\MailAdmin;

final class Domain
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        /** @var EmailEntry[] */
        public array $addresses = [],
    ) {
    }
}
