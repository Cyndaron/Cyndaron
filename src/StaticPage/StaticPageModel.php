<?php
declare(strict_types=1);

namespace Cyndaron\StaticPage;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Error\IncompleteData;
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

    public function react(string $author, string $reactie, string $antispam): bool
    {
        if ($this->id === null)
        {
            throw new IncompleteData('No ID!');
        }
        if ($this->enableComments && $author && $reactie && ($antispam === 'acht' || $antispam === '8'))
        {
            $prep = DBConnection::getPDO()->prepare('INSERT INTO sub_replies(subId, author, text) VALUES (?, ?, ?)');
            $prep->execute([$this->id, $author, $reactie]);
            return true;
        }
        return false;
    }

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
