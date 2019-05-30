<?php
namespace Cyndaron\Category;

use Cyndaron\Model;

class Category extends Model
{
    const TABLE = 'categories';
    const TABLE_FIELDS = ['name', 'description', 'onlyShowTitles', 'categoryId', 'showBreadcrumbs'];
    const HAS_CATEGORY = true;

    public $name = '';
    public $description = '';
    public $onlyShowTitles = false;
    public $categoryId = null;
    public $showBreadcrumbs = false;
}