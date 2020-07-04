<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

final class Department extends Model
{
    public const TABLE = 'geelhoed_departments';
    public const TABLE_FIELDS = ['name'];

    public const DEPARTMENT_ID_T_MULDER = 1;
    public const DEPARTMENT_ID_W_GEELHOED = 2;

    public string $name;
}
