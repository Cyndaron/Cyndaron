<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\Model;

class MenuItem extends Model
{
    const TABLE = 'menu';
    const TABLE_FIELDS = ['link', 'alias', 'isDropdown', 'isImage'];

    public $link;
    public $alias;
    public $isDropdown;
    public $isImage;
}