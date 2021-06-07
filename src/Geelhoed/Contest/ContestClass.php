<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\DBAL\Model;

class ContestClass extends Model
{
    public const TABLE = 'geelhoed_contests_classes';
    public const TABLE_FIELDS = ['name'];

    public string $name;
}
