<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\Concert;
use Cyndaron\Ticketsale\DeliveryCost\DeliveryCostInterface;
use Cyndaron\Ticketsale\TicketDelivery;
use Cyndaron\Util\BuiltinSetting;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Mail\Mail;
use Cyndaron\Util\Mail as UtilMail;
use Cyndaron\Util\Setting;
use Safe\Exceptions\JsonException;
use Symfony\Component\Mime\Address;
use function assert;
use function is_array;
use function Safe\json_decode;
use function Safe\json_encode;

final class Order extends Model
{
    public const TABLE = 'ticketsale_orders';
    public const TABLE_FIELDS = ['concertId', 'lastName', 'initials', 'email', 'street', 'houseNumber', 'houseNumberAddition', 'postcode', 'city', 'delivery', 'isDelivered', 'hasReservedSeats', 'isPaid', 'deliveryByMember', 'deliveryMemberName', 'addressIsAbroad',  'transactionCode', 'secretCode', 'comments', 'additionalData'];

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
    public string|null $transactionCode = null;
    public string|null $secretCode = null;
    public string $comments = '';
    protected string $additionalData = '';

    /** @var OrderTicketTypes[]|null  */
    private array|null $cachedTicketTypes = null;

    public function setIsPaid(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('ID is null!');
        }

        /** @var Concert|null $concert */
        $concert = Concert::fetchById($this->concertId);
        assert($concert !== null);

        $this->isPaid = true;
        $this->save();

        $organisation = Setting::get(BuiltinSetting::ORGANISATION);

        $text = "Hartelijk dank voor uw bestelling bij {$organisation}. Wij hebben uw betaling in goede orde ontvangen.\n";
        $ticketDelivery = $concert->getDelivery();
        if ($ticketDelivery === TicketDelivery::DIGITAL)
        {
            $url = $this->getLinkToTickets();
            $text .= "U kunt uw kaarten hier downloaden: {$url}\n\n";
            $text .= "Wij verzoeken u het ticket te downloaden vóórdat u de kerk binnengaat en het originele ticket te tonen. ";
            $text .= "Screenshots van de tickets kunnen wij niet goed scannen.\nDit om wachttijd te voorkomen.";
        }
        elseif ($this->delivery || ($concert->forcedDelivery && !$this->deliveryByMember))
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

        $mail = UtilMail::createMailWithDefaults(
            new Address($this->email),
            'Betalingsbevestiging',
            $text
        );
        return $mail->send();
    }

    public function setIsSent(): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('id is null!');
        }

        $result = DBConnection::getPDO()->insert('UPDATE ticketsale_orders SET `isDelivered`=1 WHERE id=?', [$this->id]);
        return (bool)$result;
    }

    /**
     * @param Concert $concert
     * @return self[]
     */
    public static function fetchByConcert(Concert $concert): array
    {
        return self::fetchAll(['concertId = ?'], [$concert->id]);
    }

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

    public function getConcert(): Concert
    {
        $concert = Concert::fetchById($this->concertId);
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
        $orderTicketTypes = $this->getTicketTypes();
        $totalCost = $this->getDeliveryCost()->getCost();
        $reservedSeatCharge = $this->hasReservedSeats ? $this->getConcert()->reservedSeatCharge : 0.00;

        foreach ($orderTicketTypes as $orderTicketType)
        {
            $ticketType = $orderTicketType->getTicketType();
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
        catch (JsonException $e)
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

    public function getPaymentLink(): string
    {
        assert($this->id !== null);
        $host = "https://{$_SERVER['HTTP_HOST']}";
        return "{$host}/concert-order/pay/{$this->id}";
    }

    public function getLinkToTickets(): string
    {
        $host = "https://{$_SERVER['HTTP_HOST']}";
        return "{$host}/concert-order/getTickets/{$this->id}/{$this->secretCode}";
    }
}
