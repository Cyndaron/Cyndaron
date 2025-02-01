<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\Concert\Concert;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Util\Error\IncompleteData;
use Safe\Exceptions\JsonException;
use function assert;
use function is_array;
use function Safe\json_decode;
use function Safe\json_encode;

final class Order extends Model
{
    public const TABLE = 'ticketsale_orders';

    #[DatabaseField(dbName: 'concertId')]
    public Concert $concert;
    #[DatabaseField]
    public string $lastName = '';
    #[DatabaseField]
    public string $initials = '';
    #[DatabaseField]
    public string $email = '';
    #[DatabaseField]
    public string $street = '';
    #[DatabaseField]
    public int $houseNumber;
    #[DatabaseField]
    public string $houseNumberAddition = '';
    #[DatabaseField]
    public string $postcode;
    #[DatabaseField]
    public string $city;
    #[DatabaseField]
    public bool $delivery;
    #[DatabaseField]
    public bool $isDelivered = false;
    #[DatabaseField]
    public bool $hasReservedSeats = false;
    #[DatabaseField]
    public bool $isPaid = false;
    #[DatabaseField]
    public bool $deliveryByMember;
    #[DatabaseField]
    public string $deliveryMemberName = '';
    #[DatabaseField]
    public bool $addressIsAbroad = false;
    #[DatabaseField]
    public string|null $transactionCode = null;
    #[DatabaseField]
    public string|null $secretCode = null;
    #[DatabaseField]
    public string $comments = '';
    #[DatabaseField]
    protected string $additionalData = '';

    /** @var OrderTicketTypes[]|null  */
    private array|null $cachedTicketTypes = null;

    /**
     * @return OrderTicketTypes[]
     */
    public function getTicketTypes(): array
    {
        if ($this->cachedTicketTypes === null)
        {
            $this->cachedTicketTypes = OrderTicketTypes::fetchAll(['orderId = ?'], [$this->id]);
        }

        return $this->cachedTicketTypes;
    }

    /**
     * @param OrderTicketTypes[] $orderTicketTypes
     * @return void
     */
    public function setTicketTypes(array $orderTicketTypes): void
    {
        $this->cachedTicketTypes = $orderTicketTypes;
    }

    public function getDeliveryCost(): DeliveryCostInterface
    {
        $interfaceName = $this->concert->getDeliveryCostInterface();
        /** @var DeliveryCostInterface $object */
        $object = new $interfaceName($this->concert, $this, $this->getTicketTypes());
        return $object;
    }

    public function calculatePrice(): float
    {
        $orderTicketTypes = $this->getTicketTypes();
        $totalCost = $this->getDeliveryCost()->getCost();
        $reservedSeatCharge = $this->hasReservedSeats ? $this->concert->reservedSeatCharge : 0.00;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $ticketType = $orderTicketType->ticketType;
            $totalCost += $orderTicketType->amount * $ticketType->price;
            $totalCost += $orderTicketType->amount * $reservedSeatCharge;
        }

        return $totalCost;
    }

    /**
     * @return array<string, mixed>
     */
    public function getAdditionalData(): array
    {
        try
        {
            $decoded = json_decode($this->additionalData, true);
            assert(is_array($decoded));
            return $decoded;
        }
        catch (JsonException)
        {
            return [];
        }
    }

    /**
     * @param array<string, mixed> $data
     * @throws JsonException
     */
    public function setAdditonalData(array $data): void
    {
        $this->additionalData = json_encode($data);
    }
}
