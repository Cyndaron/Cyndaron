<?php
namespace Cyndaron\Geelhoed;

use Cyndaron\Model;

class Sport extends Model
{
    const TABLE = 'geelhoed_sports';
    const TABLE_FIELDS = ['name'];

    public $name;
}