<?php
namespace Cyndaron\Concerts;

use Cyndaron\DBConnection;

class ShowReservedSeats
{
    public function __construct(int $concert_id)
    {
        $bookedSeats = [];

        $seatBookings = DBConnection::doQueryAndFetchAll('SELECT * FROM kaartverkoop_gereserveerde_plaatsen WHERE bestelling_id IN (SELECT id FROM kaartverkoop_bestellingen WHERE concert_id=?)', [$concert_id]);
        foreach ($seatBookings as $currentBooking)
        {
            $order = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM kaartverkoop_bestellingen WHERE id=?', [$currentBooking['bestelling_id']]);

            for ($seat = $currentBooking['eerste_stoel']; $seat <= $currentBooking['laatste_stoel']; $seat++)
            {
                $bookedSeats[$seat] = $order['voorletters'] . ' ' . $order['achternaam'] . ' (' . $order['id'] . ')';
            }
        }

        $seatRange = range(1, Util::MAX_RESERVED_SEATS);
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