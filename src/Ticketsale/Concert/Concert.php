<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Location\Location;
use Cyndaron\Ticketsale\DeliveryCost\FlatFee;
use DateTimeImmutable;
use function class_exists;

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
    #[DatabaseField(dbName: 'locationId')]
    public Location|null $location = null;
    #[DatabaseField]
    public string $ticketInfo = '';


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
