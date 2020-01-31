<?php
namespace Cyndaron\Category;

use Cyndaron\Model;

class Category extends Model
{
    public const TABLE = 'categories';
    public const TABLE_FIELDS = ['name', 'description', 'viewMode', 'categoryId', 'showBreadcrumbs'];
    public const HAS_CATEGORY = true;

    public const VIEWMODE_REGULAR = 0;
    public const VIEWMODE_TITLES = 1;
    public const VIEWMODE_BLOG = 2;
    public const VIEWMODE_PORTFOLIO = 3;
    public const VIEWMODE_HORIZONTAL = 4;

    public const VIEWMODE_DESCRIPTIONS = [
        self::VIEWMODE_REGULAR => 'Samenvatting',
        self::VIEWMODE_TITLES => 'Alleen titels',
        self::VIEWMODE_BLOG => 'Blog',
        self::VIEWMODE_PORTFOLIO => 'Portfolio',
        self::VIEWMODE_HORIZONTAL => 'Horizontaal',
    ];

    public string $name = '';
    public string $description = '';
    public int $viewMode = self::VIEWMODE_REGULAR;
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;
}