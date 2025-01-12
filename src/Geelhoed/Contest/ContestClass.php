<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Contest;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

class ContestClass extends Model
{
    public const TABLE = 'geelhoed_contests_classes';

    #[DatabaseField]
    public string $name;
}
