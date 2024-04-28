<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

enum TicketDelivery : int
{
    // Allow collection at the venue or physical delivery.
    case COLLECT_OR_DELIVER = 0;
    // Force delivery of physical ticket via the post.
    case FORCED_PHYSICAL = 1;
    // Send tickets with barcode via e-mail.
    case DIGITAL = 2;
}
