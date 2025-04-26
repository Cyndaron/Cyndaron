<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Page;

use Cyndaron\Geelhoed\Webshop\Model\OrderItem;
use Cyndaron\Geelhoed\Webshop\Model\OrderItemRepository;
use Cyndaron\Geelhoed\Webshop\Model\Product;
use Cyndaron\Geelhoed\Webshop\Model\ProductRepository;
use Cyndaron\Page\Page;
use function assert;
use function array_key_exists;
use function ksort;

final class ItemTotalsPage extends Page
{
    private readonly Product $gymtasProduct;

    public function __construct(ProductRepository $productRepository, OrderItemRepository $orderItemRepository)
    {
        $this->title = 'Bestellijst';

        $gymtasProduct = $productRepository->fetchById(Product::GYMTAS_ID);
        assert($gymtasProduct !== null);
        $this->gymtasProduct = $gymtasProduct;
        $this->addTemplateVars([
            'totalsPerProduct' => $this->calculateTotalsPerProduct($orderItemRepository),
            'products' => $productRepository->fetchAll(),
        ]);
    }

    private function getKey(OrderItem $orderItem): string|null
    {
        $product = $orderItem->product;
        if ($orderItem->product->id === Product::EXTRA_GYMTAS_ID)
        {
            $product = $this->gymtasProduct;
        }

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
    private function calculateTotalsPerProduct(OrderItemRepository $orderItemRepository): array
    {
        $totals = [];
        foreach ($orderItemRepository->fetchAll() as $orderItem)
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
