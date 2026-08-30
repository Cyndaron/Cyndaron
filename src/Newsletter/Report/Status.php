<?php
declare(strict_types=1);

namespace Cyndaron\Newsletter\Report;

enum Status
{
    case ADDRESS_DOES_NOT_EXIST;
    case ADDRESS_FORMAT_INVALID;
    case DOMAIN_DOES_NOT_EXIST;
    case MAILBOX_FULL;
}
