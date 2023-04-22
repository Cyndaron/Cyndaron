<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\CacheableModel;

final class Sport extends CacheableModel
{
    public const TABLE = 'geelhoed_sports';
    public const TABLE_FIELDS = ['name', 'juniorFee', 'seniorFee'];

    public string $name;
    public float $juniorFee;
    public float $seniorFee;

    public function __toString(): string
    {
        return $this->name;
    }
}
