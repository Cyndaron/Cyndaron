<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DatabaseField;
use function explode;
use function implode;
use function strtolower;

final class StaticPageModel extends ModelWithCategory
{
    public const TABLE = 'subs';
    public const CATEGORY_TABLE = 'sub_categories';

    #[DatabaseField]
    public string $text = '';
    #[DatabaseField]
    public bool $enableComments = false;
    #[DatabaseField]
    public string $tags = '';

    /**
     * @return string[]
     */
    public function getTagList(): array
    {
        if (empty($this->tags))
        {
            return [];
        }

        return explode(';', strtolower($this->tags));
    }

    /**
     * @param string[] $tags
     */
    public function setTagList(array $tags): void
    {
        $this->tags = implode(';', $tags);
    }

    public function getText(): string
    {
        return $this->text;
    }
}
