<?php
namespace Cyndaron\Category;

use Cyndaron\Model;

class Category extends Model
{
    const TABLE = 'categories';
    const TABLE_FIELDS = ['name', 'description', 'viewMode', 'categoryId', 'showBreadcrumbs'];
    const HAS_CATEGORY = true;

    const VIEWMODE_REGULAR = 0;
    const VIEWMODE_TITLES = 1;
    const VIEWMODE_BLOG = 2;
    const VIEWMODE_PORTFOLIO = 3;

    public $name = '';
    public $description = '';
    public $viewMode = 0;
    public $categoryId = null;
    public $showBreadcrumbs = false;
}