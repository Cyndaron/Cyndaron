<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter;

enum RecipientGroup : string
{
    case SINGLE = 'single';
    case SUBSCRIBERS = 'subscribers';
    case EVERYONE = 'everyone';
}
