<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

final class EmailPresenceStatistics
{
    public function __construct(
        public readonly int $users,
        public readonly int $members,
        public readonly int $subscribers,
    ) {
    }

    public function total(): int
    {
        return $this->users + $this->members + $this->subscribers;
    }
}
