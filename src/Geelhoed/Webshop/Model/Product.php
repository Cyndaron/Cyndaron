<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Webshop\Model;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;

final class Product extends Model
{
    use FileCachedModel;

    public const GYMTAS_ID = 1;
    public const DONATE_TICKETS_ID = 18;

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

    public function getParent(): self|null
    {
        if ($this->parentId === null)
        {
            return null;
        }

        return self::fetchById($this->parentId);
    }

    public function tryGetGcaTicketPrice(): int|null
    {
        if ($this->gcaTicketPrice !== null)
        {
            return $this->gcaTicketPrice;
        }

        $parent = $this->getParent();
        return $parent?->gcaTicketPrice;

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
        if ($this->euroPrice !== null)
        {
            return $this->euroPrice;
        }

        $parent = $this->getParent();
        return $parent?->euroPrice;

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
