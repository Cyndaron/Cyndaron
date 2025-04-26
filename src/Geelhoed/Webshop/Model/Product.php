<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Product extends Model
{
    public const GYMTAS_ID = 1;
    public const DONATE_TICKETS_ID = 18;
    public const EXTRA_GYMTAS_ID = 19;

    public const TABLE = 'geelhoed_webshop_product';

    #[DatabaseField]
    public int|null $parentId = null;
    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public string $description;
    #[DatabaseField]
    public string $options;
    #[DatabaseField]
    public int|null $gcaTicketPrice = null;
    #[DatabaseField]
    public float|null $euroPrice = null;
    #[DatabaseField]
    public bool $visible = true;

    public const OPTION_MAPPING = [
        'color' => 'Kleur',
        'size' => 'Maat',
    ];

    public function tryGetGcaTicketPrice(): int|null
    {
        return $this->gcaTicketPrice;

    }

    public function getGcaTicketPrice(): int
    {
        $gcaTicketPrice = $this->tryGetGcaTicketPrice();
        if ($gcaTicketPrice === null)
        {
            throw new \Exception('No price set!');
        }

        return $gcaTicketPrice;
    }

    public function tryGetEuroPrice(): float|null
    {
        return $this->euroPrice;
    }

    public function getEuroPrice(): float
    {
        $euroPrice = $this->tryGetEuroPrice();
        if ($euroPrice === null)
        {
            throw new \Exception('No price set!');
        }

        return $euroPrice;
    }

    public function isMainProduct(): bool
    {
        return $this->parentId === null;
    }

    public function isVariant(): bool
    {
        return $this->parentId !== null;
    }

    /**
     * @return array<string, string[]>
     */
    public function getOptions(): array
    {
        /** @var array<string, string[]> $decoded */
        $decoded = \Safe\json_decode($this->options, true, options: JSON_THROW_ON_ERROR);
        return $decoded;
    }
}
