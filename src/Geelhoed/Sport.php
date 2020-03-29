<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

class Sport extends Model
{
    public const TABLE = 'geelhoed_sports';
    public const TABLE_FIELDS = ['name', 'juniorFee', 'seniorFee'];

    public string $name;
    public float $juniorFee;
    public float $seniorFee;
}