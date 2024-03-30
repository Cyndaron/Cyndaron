<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;

final class RichLink extends ModelWithCategory
{
    public const TABLE = 'richlink';
    public const CATEGORY_TABLE = 'richlink_category';
    public const TABLE_FIELDS = ['name', 'url', 'previewImage', 'blurb', 'openInNewTab'];

    public string $url = '';

    public function getFriendlyUrl(UrlService $urlService): Url
    {
        return new Url($this->url);
    }

    public function getText(): string
    {
        return $this->blurb;
    }
}
