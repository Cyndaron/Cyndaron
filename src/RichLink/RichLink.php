<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\ModelWithCategory;

final class RichLink extends ModelWithCategory
{
    public const TABLE = 'richlink';
    public const CATEGORY_TABLE = 'richlink_category';
    public const TABLE_FIELDS = ['name', 'url', 'previewImage', 'blurb', 'openInNewTab'];

    public string $url = '';

    public function getFriendlyUrl(): string
    {
        return $this->url;
    }

    public function getText(): string
    {
        return $this->blurb;
    }
}
