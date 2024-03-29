<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

final class TicketDelivery
{
    // Allow collection at the venue or physical delivery.
    public const COLLECT_OR_DELIVER = 0;
    // Force delivery of physical ticket via the post.
    public const FORCED_PHYSICAL = 1;
    // Send tickets with barcode via e-mail.
    public const DIGITAL = 2;
}
