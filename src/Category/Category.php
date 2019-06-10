<?php
namespace Cyndaron\Category;

use Cyndaron\Model;

class Category extends Model
{
    const TABLE = 'categories';
    const TABLE_FIELDS = ['name', 'description', 'viewMode', 'categoryId', 'showBreadcrumbs'];
    const HAS_CATEGORY = true;

    public $name = '';
    public $description = '';
    public $viewMode = 0;
    public $categoryId = null;
    public $showBreadcrumbs = false;
}