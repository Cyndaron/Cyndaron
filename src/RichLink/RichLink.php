<?php
declare(strict_types=1);

namespace Cyndaron\RichLink;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;

final class RichLink extends ModelWithCategory
{
    public const TABLE = 'richlink';
    public const CATEGORY_TABLE = 'richlink_category';

    #[DatabaseField]
    public bool $openInNewTab = false;

    #[DatabaseField]
    public string $url = '';

    public function getText(): string
    {
        return $this->blurb;
    }

    public function shouldOpenInNewTab(): bool
    {
        return $this->openInNewTab;
    }
}
