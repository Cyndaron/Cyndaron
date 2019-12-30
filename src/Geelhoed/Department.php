<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

class Department extends Model
{
    const TABLE = 'geelhoed_departments';
    const TABLE_FIELDS = ['name'];

    public $name;
}