<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

enum OrderStatus : string
{
    case QUOTE = 'quote';
    case PENDING_TICKET_CHECK = 'pending_ticket_check';
    case PENDING_PAYMENT = 'pending_payment';
    case IN_PROGRESS = 'in_progress';
    case DELIVERED = 'delivered';

    public function getDescription(): string
    {
        return match($this)
        {
            self::QUOTE => 'Gebruiker is nog bezig',
            self::PENDING_TICKET_CHECK => 'Wacht op lotencheck',
            self::PENDING_PAYMENT => 'Wacht op betaling',
            self::IN_PROGRESS => 'Wordt door ons verwerkt',
            self::DELIVERED => 'Meegegeven aan docent',
        };
    }
}
