<?php
declare(strict_types=1);

namespace Cyndaron\Menu;

use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\Model;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
use Cyndaron\Util\Setting;
use function is_string;
use function ltrim;
use function strpos;
use function str_starts_with;

final class MenuItem extends Model
{
    public const TABLE = 'menu';

    #[DatabaseField]
    public string $link;
    #[DatabaseField]
    public string|null $alias = null;
    #[DatabaseField]
    public bool $isDropdown = false;
    #[DatabaseField]
    public bool $isImage = false;
    #[DatabaseField]
    public int|null $priority = null;

    public function getTitle(UrlService $urlService): string
    {
        if (is_string($this->alias) && !empty($this->alias))
        {
            return $this->alias;
        }

        $url = new Url($this->link);
        return $urlService->getPageTitle($url);
    }

    public function getLink(): Url
    {
        if ($this->link === Setting::get('frontPage'))
        {
            return new Url('/');
        }

        $link = $this->link;
        // Do not put a slash in front of URLs that already include the protocol.
        if (strpos($link, ':/'))
        {
            return new Url($link);
        }

        // For relative URLs, ensure there is a slash in front.
        return new Url('/' . ltrim($link, '/'));
    }

    public function isCategoryDropdown(): bool
    {
        return str_starts_with($this->link, '/category/') && $this->isDropdown;
    }
}
