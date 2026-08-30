<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter\Report;

final class Result
{
    public function __construct(
        public readonly int $messageUid,
        public readonly string $email,
        public Status $status,
        public Action $proposedAction,
    ) {
    }
}
