<?php
namespace Cyndaron\Category;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Photoalbum\Photoalbum;
use Cyndaron\RichLink\RichLink;
use Cyndaron\StaticPage\StaticPageModel;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use function array_merge;
use function strcasecmp;
use function usort;

final class Category extends ModelWithCategory
{
    public const TABLE = 'categories';
    public const CATEGORY_TABLE = 'category_categories';

    #[DatabaseField]
    public string $description = '';
    #[DatabaseField]
    public ViewMode $viewMode = ViewMode::Regular;

    public function getText(): string
    {
        return $this->description;
    }
}
