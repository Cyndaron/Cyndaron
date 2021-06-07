<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use function range;

final class ShowReservedSeats
{
    private string $contents = '';

    public function __construct(Concert $concert)
    {
        $bookedSeats = [];

        $seatBookings = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_reservedseats WHERE orderId IN (SELECT id FROM ticketsale_orders WHERE concertId=?)', [$concert->id]) ?: [];
        foreach ($seatBookings as $currentBooking)
        {
            /** @var Order|null $order */
            $order = Order::loadFromDatabase($currentBooking['orderId']);
            if ($order === null)
            {
                continue;
            }

            for ($seat = $currentBooking['eerste_stoel']; $seat <= $currentBooking['laatste_stoel']; $seat++)
            {
                $bookedSeats[$seat] = $order->initials . ' ' . $order->lastName . ' (' . $order->id. ')';
            }
        }

        $seatRange = range(1, $concert->numReservedSeats);
        foreach ($seatRange as $seat)
        {
            $this->contents .= '<div style="display: inline-block; text-align: center; width: 220px; margin: 5px;">' . $seat . '<br>';
            if (isset($bookedSeats[$seat]))
            {
                $this->contents .= $bookedSeats[$seat];
            }
            else
            {
                $this->contents .= '&nbsp;';
            }
            $this->contents .= '</div>';
        }
    }

    public function render(): string
    {
        return $this->contents;
    }
}
