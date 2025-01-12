<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;

final class Department extends Model
{
    public const TABLE = 'geelhoed_departments';

    public const DEPARTMENT_ID_T_MULDER = 1;
    public const DEPARTMENT_ID_W_GEELHOED = 2;

    #[DatabaseField]
    public string $name;
}
