<?php
namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\DBAL\FileCachedModel;
use Cyndaron\DBAL\Model;

final class Sport extends Model
{
    use FileCachedModel;

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
