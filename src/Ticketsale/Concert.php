<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Error\IncompleteData;
use Cyndaron\DBAL\Model;
use Exception;
use function range;

final class Concert extends Model
{
    public const TABLE = 'ticketsale_concerts';
    public const TABLE_FIELDS = ['name', 'openForSales', 'description', 'descriptionWhenClosed', 'deliveryCost', 'forcedDelivery', 'hasReservedSeats', 'reservedSeatCharge', 'reservedSeatsAreSoldOut', 'numFreeSeats', 'numReservedSeats'];

    public string $name = '';
    public bool $openForSales = true;
    public string $description = '';
    public string $descriptionWhenClosed = '';
    public float $deliveryCost = 1.50;
    public bool $forcedDelivery = true;
    public bool $hasReservedSeats = true;
    public float $reservedSeatCharge = 5.00;
    public bool $reservedSeatsAreSoldOut = false;
    public int $numFreeSeats = 250;
    public int $numReservedSeats = 270;

    /**
     * @param int $orderId
     * @param int $numTickets
     * @throws Exception
     * @return int[]|null Which seats were reserved, if there were enough, null otherwise
     */
    public function reserveSeats(int $orderId, int $numTickets): ?array
    {
        if (!$this->id)
        {
            throw new IncompleteData('No ID!');
        }

        $foundEnoughSeats = false;
        $reservedSeats = [];

        $reservedSeatsPerOrder = DBConnection::doQueryAndFetchAll('SELECT * FROM ticketsale_reservedseats WHERE orderId IN (SELECT id FROM ticketsale_orders WHERE concertId=?)', [$this->id]) ?: [];
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
        for ($stoel = 1; $stoel <= $this->numReservedSeats; $stoel++)
        {
            if (isset($reservedSeats[$stoel]) && $reservedSeats[$stoel] === true)
            {
                $adjacentFreeSeats = 0;
            }
            else
            {
                $adjacentFreeSeats++;
            }

            if ($adjacentFreeSeats === $numTickets)
            {
                $foundEnoughSeats = true;
                $firstSeat = $stoel - $numTickets + 1;
                $lastSeat = $stoel;
                break;
            }
        }

        if ($foundEnoughSeats)
        {
            DBConnection::doQuery('INSERT INTO ticketsale_reservedseats(`orderId`, `row`, `firstSeat`, `lastSeat`) VALUES(?, \'A\', ?, ?)', [$orderId, $firstSeat, $lastSeat]);
            return range($firstSeat, $lastSeat);
        }

        return null;
    }
}
