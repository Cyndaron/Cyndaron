<?php
declare (strict_types = 1);

namespace Cyndaron\Kaartverkoop;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class Concert extends Model
{
    protected static $table = 'kaartverkoop_concerten';

    /**
     * @param int $numTickets
     * @param $orderId
     * @return array|null Which seats were reserved, if there were enough, null otherwise
     * @throws \Exception
     */
    public function reserveSeats(int $orderId, int $numTickets): ?array
    {
        if (!$this->id)
        {
            throw new \Exception('No ID!');
        }

        $foundEnoughSeats = false;
        $reservedSeats = [];

        $reservedSeatsPerOrder = DBConnection::doQueryAndFetchAll('SELECT * FROM kaartverkoop_gereserveerde_plaatsen WHERE bestelling_id IN (SELECT id FROM kaartverkoop_bestellingen WHERE concert_id=?)', [$this->id]);
        foreach ($reservedSeatsPerOrder as $reservedSeatsForThisOrder)
        {
            for ($i = $reservedSeatsForThisOrder['eerste_stoel']; $i <= $reservedSeatsForThisOrder['laatste_stoel']; $i++)
            {
                $reservedSeats[$i] = true;
            }
        }

        $firstSeat = 0;
        $lastSeat = 0;

        $adjacentFreeSeats = 0;
        for ($stoel = 1; $stoel <= Util::MAX_RESERVED_SEATS; $stoel++)
        {
            if (isset($reservedSeats[$stoel]) && $reservedSeats[$stoel] == true)
            {
                $adjacentFreeSeats = 0;
            }
            else
            {
                $adjacentFreeSeats++;
            }

            if ($adjacentFreeSeats == $numTickets)
            {
                $foundEnoughSeats = true;
                $firstSeat = $stoel - $numTickets + 1;
                $lastSeat = $stoel;
                break;
            }
        }

        if ($foundEnoughSeats)
        {
            DBConnection::doQuery('INSERT INTO kaartverkoop_gereserveerde_plaatsen(`bestelling_id`, `rij`, `eerste_stoel`, `laatste_stoel`) VALUES(?, \'A\', ?, ?)', [$orderId, $firstSeat, $lastSeat]);
            return range($firstSeat, $lastSeat);
        }
        else
        {
            return null;
        }
    }
}