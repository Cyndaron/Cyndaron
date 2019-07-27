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
    const VIEWMODE_HORIZONTAL = 4;

    const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Samenvatting',
        self::VIEWMODE_TITLES => 'Alleen titels',
        self::VIEWMODE_BLOG => 'Blog',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
        self::VIEWMODE_HORIZONTAL => 'Horizontaal',
    ];

    public $name = '';
    public $description = '';
    public $viewMode = 0;
    public $categoryId = null;
    public $showBreadcrumbs = false;
}