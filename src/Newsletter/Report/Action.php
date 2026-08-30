<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter\Report;

enum Action
{
    case DELETE_ADDRESS;
    case UNSUBSCRIBE;
    case WAIT;

    public function canBeApplied(): bool
    {
        return match ($this)
        {
            self::DELETE_ADDRESS, self::UNSUBSCRIBE => true,
            self::WAIT => false,
        };
    }
}
