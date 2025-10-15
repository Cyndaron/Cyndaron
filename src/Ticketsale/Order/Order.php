<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Order;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Ticketsale\Concert\Concert;
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
    public string $additionalData = '';

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
