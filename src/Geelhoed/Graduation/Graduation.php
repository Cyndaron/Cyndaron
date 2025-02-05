<?php
declare(strict_types=1);

namespace Cyndaron\Geelhoed\Graduation;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Geelhoed\Sport\Sport;

final class Graduation extends Model
{
    public const TABLE = 'geelhoed_graduations';

    #[DatabaseField(dbName: 'sportId')]
    public Sport $sport;
    #[DatabaseField]
    public string $name;
}
