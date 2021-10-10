<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Ticketsale\DeliveryCost\FlatFee;
use Cyndaron\Ticketsale\TicketType;
use Cyndaron\Util\Mail\Mail;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\View\Page;
use Safe\Exceptions\JsonException;
use Symfony\Component\Mime\Address;
use function array_key_exists;
use function assert;
use function class_exists;
use function Safe\json_decode;
use function Safe\json_encode;

final class Order extends Model
{
    public const TABLE = 'ticketsale_orders';
    public const TABLE_FIELDS = ['concertId', 'lastName', 'initials', 'email', 'street', 'houseNumber', 'houseNumberAddition', 'postcode', 'city', 'delivery', 'isDelivered', 'hasReservedSeats', 'isPaid', 'deliveryByMember', 'deliveryMemberName', 'addressIsAbroad', 'comments', 'additionalData'];

    public int $concertId;
    public string $lastName = '';
    public string $initials = '';
    public string $email = '';
    public string $street = '';
    public int $houseNumber;
    public string $houseNumberAddition = '';
    public string $postcode;
    public string $city;
    public bool $delivery;
    public bool $isDelivered = false;
    public bool $hasReservedSeats = false;
    public bool $isPaid = false;
    public bool $deliveryByMember;
    public string $deliveryMemberName = '';
    public bool $addressIsAbroad = false;
    public string $comments = '';
    protected string $additionalData = '';

    private ?array $cachedTicketTypes = null;

    /** @var TicketType[] */
    private static array $ticketTypeCache = [];

    public function setIsPaid(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is null!');
        }

        /** @var Concert|null $concert */
        $concert = Concert::loadFromDatabase($this->concertId);
        assert($concert !== null);

        DBConnection::doQuery('UPDATE ticketsale_orders SET `isPaid`=1 WHERE id=?', [$this->id]);

        $text = "Hartelijk dank voor uw bestelling bij de Vlissingse Oratorium Vereniging. Wij hebben uw betaling in goede orde ontvangen.\n";
        if ($this->delivery || ($concert->forcedDelivery && !$this->deliveryByMember))
        {
            $text .= 'Uw kaarten zullen zo spoedig mogelijk worden opgestuurd.';
        }
        elseif ($concert->forcedDelivery && $this->deliveryByMember)
        {
            $text .= 'Uw kaarten zullen worden meegegeven aan ' . $this->deliveryMemberName . '.';
        }
        else
        {
            $text .= 'Uw kaarten zullen op de avond van het concert voor u klaarliggen bij de kassa.';
        }

        $mail = new Mail(new Address($this->email), 'Betalingsbevestiging', $text);
        return $mail->send();
    }

    public function setIsSent(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('id is null!');
        }

        $result = DBConnection::doQuery('UPDATE ticketsale_orders SET `isDelivered`=1 WHERE id=?', [$this->id]);
        return (bool)$result;
    }

    public static function fetchByConcert(Concert $concert): array
    {
        return self::fetchAll(['concertId = ?'], [$concert->id]);
    }

    private static function getTicketType(int $ticketTypeId): TicketType
    {
        if (!array_key_exists($ticketTypeId, self::$ticketTypeCache))
        {
            $ticketType = TicketType::loadFromDatabase($ticketTypeId);
            assert($ticketType !== null);
            self::$ticketTypeCache[$ticketTypeId] = $ticketType;
        }

        return self::$ticketTypeCache[$ticketTypeId];
    }

    /**
     * @return OrderTicketType[]
     */
    public function getTicketTypes(): array
    {
        if ($this->cachedTicketTypes !== null)
        {
            return $this->cachedTicketTypes;
        }

        $this->cachedTicketTypes = [];
        $records = DBConnection::doQueryAndFetchAll('SELECT * FROM `ticketsale_orders_tickettypes` WHERE `orderId` = ?', [$this->id]) ?: [];
        foreach ($records as $record)
        {
            $ticketType = self::getTicketType((int)$record['tickettypeId']);

            $ott = new OrderTicketType();
            $ott->order = $this;
            $ott->ticketType = $ticketType;
            $ott->amount = $record['amount'];

            $this->cachedTicketTypes[] = $ott;
        }

        return $this->cachedTicketTypes;
    }

    public function setTicketTypes(array $orderTicketTypes): void
    {
        $this->cachedTicketTypes = $orderTicketTypes;
    }

    public function getConcert(): Concert
    {
        $concert = Concert::loadFromDatabase($this->concertId);
        assert($concert !== null);
        return $concert;
    }

    public function getDeliveryCost(): DeliveryCostInterface
    {
        $concert = $this->getConcert();
        $interfaceName = $concert->getDeliveryCostInterface();
        /** @var DeliveryCostInterface $object */
        $object = new $interfaceName($concert, $this, $this->getTicketTypes());
        return $object;
    }

    public function calculatePrice(): float
    {
        $ticketTypes = $this->getTicketTypes();
        $totalCost = $this->getDeliveryCost()->getCost();
        $reservedSeatCharge = $this->hasReservedSeats ? $this->getConcert()->reservedSeatCharge : 0.00;

        foreach ($ticketTypes as $ticketType)
        {
            $totalCost += $ticketType->amount * $ticketType->ticketType->price;
            $totalCost += $ticketType->amount * $reservedSeatCharge;
        }

        return $totalCost;
    }

    public function getAdditionalData(): array
    {
        try
        {
            return json_decode($this->additionalData, true);
        }
        catch (JsonException $e)
        {
            return [];
        }
    }

    /**
     * @param array $data
     * @throws JsonException
     */
    public function setAdditonalData(array $data): void
    {
        $this->additionalData = json_encode($data);
    }
}
