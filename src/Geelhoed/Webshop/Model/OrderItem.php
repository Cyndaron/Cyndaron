<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;
use function assert;

final class OrderItem extends Model
{
    use FileCachedModel;

    public const TABLE = 'geelhoed_webshop_order_item';

    #[DatabaseField]
    public int $orderId;
    #[DatabaseField]
    public int $productId;
    #[DatabaseField]
    public int $quantity;
    #[DatabaseField]
    public string $options = '{}';
    #[DatabaseField]
    public float $price;
    #[DatabaseField]
    public Currency $currency;

    public function getProduct(): Product
    {
        $product = Product::fetchById($this->productId);
        assert($product !== null);
        return $product;
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        /** @var array<string, string> $options */
        $options = \Safe\json_decode($this->options, true, options: JSON_THROW_ON_ERROR);
        return $options;
    }

    public function getLineDescription(): string
    {
        $description = $this->getProduct()->name;
        foreach ($this->getOptions() as $option)
        {
            $description .= ", {$option}";
        }
        return $description;
    }

    /**
     * @return self[]
     */
    public static function fetchAllByOrder(Order $order): array
    {
        return self::fetchAll(['orderId = ?'], [$order->id]);
    }

    public function equals(OrderItem $otherItem): bool
    {
        return
            $this->currency === $otherItem->currency &&
            $this->price === $otherItem->price &&
            $this->productId === $otherItem->productId &&
            $this->orderId === $otherItem->orderId &&
            $this->getOptions() === $otherItem->getOptions();
    }

    public function getLineAmount(): float
    {
        return $this->price * $this->quantity;
    }
}
