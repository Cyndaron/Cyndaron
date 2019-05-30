<?php
declare (strict_types = 1);

namespace Cyndaron\Menu;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class MenuItem extends Model
{
    const TABLE = 'menu';
    const TABLE_FIELDS = ['link', 'alias', 'isDropdown', 'isImage', 'priority'];

    public $link;
    public $alias;
    public $isDropdown;
    public $isImage;
    public $priority;

    public function save(): bool
    {
        if (!isset($this->priority) || $this->priority === '') {
            $priority = DBConnection::doQueryAndFetchOne('SELECT MAX(priority) FROM menu WHERE id <> ?', [$this->id]) + 1;
            $this->priority = $priority;
        }
        return parent::save();
    }
}