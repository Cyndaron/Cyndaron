<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\DBAL\Model;
use Cyndaron\Util\Mail\Mail;
use Cyndaron\Util\Error\IncompleteData;
use Symfony\Component\Mime\Address;
use function assert;

final class Order extends Model
{
    public const TABLE = 'ticketsale_orders';
    public const TABLE_FIELDS = ['concertId', 'lastName', 'initials', 'email', 'street', 'houseNumber', 'houseNumberAddition', 'postcode', 'city', 'delivery', 'isDelivered', 'hasReservedSeats', 'isPaid', 'deliveryByMember', 'deliveryMemberName', 'addressIsAbroad', 'comments'];

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
}
