<?php
namespace Cyndaron\Geelhoed\Sport;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Sport extends Model
{
    public const TABLE = 'geelhoed_sports';

    #[DatabaseField]
    public string $name;
    #[DatabaseField]
    public float $juniorFee;
    #[DatabaseField]
    public float $seniorFee;

    public function __toString(): string
    {
        return $this->name;
    }
}
