<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\DeliveryCost\FlatFee;
use Cyndaron\Util\Error\IncompleteData;
use DateTimeImmutable;
use Exception;
use function class_exists;
use function range;

final class Concert extends Model
{
    public const TABLE = 'ticketsale_concerts';

    #[DatabaseField]
    public string $name = '';
    #[DatabaseField]
    public bool $openForSales = true;
    #[DatabaseField]
    public string $description = '';
    #[DatabaseField]
    public string $descriptionWhenClosed = '';
    #[DatabaseField]
    public float $deliveryCost = 1.50;
    #[DatabaseField]
    public bool $forcedDelivery = true;
    #[DatabaseField]
    public bool $digitalDelivery = false;
    #[DatabaseField]
    public bool $hasReservedSeats = true;
    #[DatabaseField]
    public float $reservedSeatCharge = 5.00;
    #[DatabaseField]
    public bool $reservedSeatsAreSoldOut = false;
    #[DatabaseField]
    public int $numFreeSeats = 250;
    #[DatabaseField]
    public int $numReservedSeats = 270;
    #[DatabaseField]
    public string $deliveryCostInterface = '';
    #[DatabaseField]
    public string $secretCode = '';
    #[DatabaseField]
    public string $date = '';
    #[DatabaseField]
    public int $locationId = 0;
    #[DatabaseField]
    public string $ticketInfo = '';


    /**
     * @param int $orderId
     * @param int $numTickets
     * @throws Exception
     * @return int[]|null Which seats were reserved, if there were enough, null otherwise
     */
    public function reserveSeats(int $orderId, int $numTickets):array|null
    {
        if (!$this->id)
        {
            throw new IncompleteData('No ID!');
        }

        $foundEnoughSeats = false;
        $reservedSeats = [];

        $reservedSeatsPerOrder = DBConnection::getPDO()->doQueryAndFetchAll('SELECT * FROM ticketsale_reservedseats WHERE orderId IN (SELECT id FROM ticketsale_orders WHERE concertId=?)', [$this->id]) ?: [];
        foreach ($reservedSeatsPerOrder as $reservedSeatsForThisOrder)
        {
            for ($i = $reservedSeatsForThisOrder['firstSeat']; $i <= $reservedSeatsForThisOrder['lastSeat']; $i++)
            {
                $reservedSeats[$i] = true;
            }
        }

        $firstSeat = 0;
        $lastSeat = 0;

        $adjacentFreeSeats = 0;
        for ($stoel = 1; $stoel <= $this->numReservedSeats; $stoel++)
        {
            if (($reservedSeats[$stoel] ?? false) === true)
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
            DBConnection::getPDO()->executeQuery('INSERT INTO ticketsale_reservedseats(`orderId`, `row`, `firstSeat`, `lastSeat`) VALUES(?, \'A\', ?, ?)', [$orderId, $firstSeat, $lastSeat]);
            return range($firstSeat, $lastSeat);
        }

        return null;
    }

    /**
     * @return class-string
     */
    public function getDeliveryCostInterface(): string
    {
        $interfaceName = $this->deliveryCostInterface;
        if (class_exists($interfaceName))
        {
            return $interfaceName;
        }

        return FlatFee::class;
    }

    public function getDelivery(): TicketDelivery
    {
        if ($this->digitalDelivery)
        {
            return TicketDelivery::DIGITAL;
        }
        if ($this->forcedDelivery)
        {
            return TicketDelivery::FORCED_PHYSICAL;
        }

        return TicketDelivery::COLLECT_OR_DELIVER;
    }

    public function getDate(): DateTimeImmutable
    {
        $result = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $this->date);
        if ($result === false)
        {
            return new DateTimeImmutable();
        }
        return $result;
    }
}
