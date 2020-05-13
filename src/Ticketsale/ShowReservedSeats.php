<?php
namespace Cyndaron\Ticketsale;

use Cyndaron\DBConnection;

class ShowReservedSeats
{
    public function __construct(Concert $concert)
    {
        $concert->load();

        $bookedSeats = [];

        $seatBookings = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_reservedseats WHERE orderId IN (SELECT id FROM ticketsale_orders WHERE concertId=?)', [$concertId]);
        foreach ($seatBookings as $currentBooking)
        {
            $order = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM ticketsale_orders WHERE id=?', [$currentBooking['orderId']]);

            for ($seat = $currentBooking['eerste_stoel']; $seat <= $currentBooking['laatste_stoel']; $seat++)
            {
                $bookedSeats[$seat] = $order['initials'] . ' ' . $order['lastName'] . ' (' . $order['id'] . ')';
            }
        }

        $seatRange = range(1, $concert->numReservedSeats);
        foreach ($seatRange as $seat)
        {
            echo '<div style="display: inline-block; text-align: center; width: 220px; margin: 5px;">' . $seat . '<br>';
            if (isset($bookedSeats[$seat]))
            {
                echo $bookedSeats[$seat];
            }
            else
            {
                echo '&nbsp;';
            }
            echo '</div>';
        }
    }
}