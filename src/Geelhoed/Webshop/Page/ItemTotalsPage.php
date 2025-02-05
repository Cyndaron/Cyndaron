<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Page\Page;
use function assert;
use function array_key_exists;
use function ksort;

final class ItemTotalsPage extends Page
{
    public function __construct()
    {
        $this->title = 'Bestellijst';
        $this->addTemplateVars([
            'totalsPerProduct' => $this->calculateTotalsPerProduct(),
            'products' => Product::fetchAll(),
        ]);
    }

    private function getKey(OrderItem $orderItem): string|null
    {
        $productId = $orderItem->product->id;
        assert($productId !== null);
        if ($productId === Product::EXTRA_GYMTAS_ID)
        {
            $productId = Product::GYMTAS_ID;
        }
        $product = Product::fetchById($productId);
        assert($product !== null);

        $key = $product->name;
        $options = $orderItem->getOptions();
        foreach ($options as $optionName => $option)
        {
            if ($option === 'Achterwege laten')
            {
                return null;
            }

            $optionName = Product::OPTION_MAPPING[$optionName] ?? $optionName;
            $key .= ", {$optionName}: {$option}";
        }

        return $key;
    }

    /**
     * @return array<string, int>
     */
    private function calculateTotalsPerProduct(): array
    {
        $totals = [];
        foreach (OrderItem::fetchAll() as $orderItem)
        {
            if ($orderItem->product->id === Product::DONATE_TICKETS_ID)
            {
                continue;
            }

            $key = $this->getKey($orderItem);
            if ($key === null)
            {
                continue;
            }

            if (!array_key_exists($key, $totals))
            {
                $totals[$key] = 0;
            }

            $totals[$key] += $orderItem->quantity;
        }

        ksort($totals);
        return $totals;
    }
}
