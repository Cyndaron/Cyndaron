<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBAL\DBConnection;
use Cyndaron\Util\Error\IncompleteData;
use Cyndaron\Category\ModelWithCategory;
use Cyndaron\Url;
use function explode;
use function strtolower;
use function implode;

final class StaticPageModel extends ModelWithCategory
{
    public const TABLE = 'subs';
    public const CATEGORY_TABLE = 'sub_categories';
    public const TABLE_FIELDS = ['name', 'image', 'previewImage', 'blurb', 'text', 'enableComments', 'showBreadcrumbs', 'tags'];

    public string $text = '';
    public bool $enableComments = false;
    public string $tags = '';

    public function delete(): void
    {
        parent::delete();
        DBConnection::getPDO()->executeQuery('DELETE FROM sub_backups WHERE id = ?', [$this->id]);
    }

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

    public function getFriendlyUrl(): string
    {
        $url = new Url('/sub/' . $this->id);
        return $url->getFriendly();
    }

    public function getText(): string
    {
        return $this->text;
    }
}
