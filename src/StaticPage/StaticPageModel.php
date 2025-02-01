<?php
namespace Cyndaron\StaticPage;

use Cyndaron\Category\ModelWithCategory;
use Cyndaron\DBAL\DatabaseField;
use Cyndaron\DBAL\DBConnection;
use Cyndaron\Url\Url;
use Cyndaron\Url\UrlService;
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

    public function save(): bool
    {
        $oldData = null;
        if ($this->id !== null)
        {
            $oldData = self::fetchById($this->id);
        }
        $result = parent::save();
        if ($result && $oldData !== null)
        {
            DBConnection::getPDO()->executeQuery('REPLACE INTO sub_backups(`id`, `name`, `text`) VALUES (?,?,?)', [$oldData->id, $oldData->name, $oldData->text]);
        }
        return $result;
    }

    public function hasBackup(): bool
    {
        return (bool)DBConnection::getPDO()->doQueryAndFetchOne('SELECT * FROM sub_backups WHERE id= ?', [$this->id]);
    }

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

    public function getFriendlyUrl(UrlService $urlService): Url
    {
        $url = new Url('/sub/' . $this->id);
        return $urlService->toFriendly($url);
    }

    public function getText(): string
    {
        return $this->text;
    }
}
