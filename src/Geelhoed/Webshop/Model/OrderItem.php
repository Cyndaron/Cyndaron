<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class OrderItem extends Model
{
    public const TABLE = 'geelhoed_webshop_order_item';

    #[DatabaseField(dbName: 'orderId')]
    public Order $order;
    #[DatabaseField(dbName: 'productId')]
    public Product $product;
    #[DatabaseField]
    public int $quantity;
    #[DatabaseField]
    public string $options = '{}';
    #[DatabaseField]
    public float $price;
    #[DatabaseField]
    public Currency $currency;

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
        $description = $this->product->name;
        foreach ($this->getOptions() as $option)
        {
            $description .= ", {$option}";
        }
        return $description;
    }

    public function equals(OrderItem $otherItem): bool
    {
        return
            $this->currency === $otherItem->currency &&
            $this->price === $otherItem->price &&
            $this->product->id === $otherItem->product->id &&
            $this->order->id === $otherItem->order->id &&
            $this->getOptions() === $otherItem->getOptions();
    }

    public function getLineAmount(): float
    {
        return $this->price * $this->quantity;
    }
}
