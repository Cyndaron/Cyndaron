<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Error\IncompleteData;
use Cyndaron\ModelWithCategory;
use Cyndaron\Template\ViewHelpers;
use Cyndaron\Url;

class StaticPageModel extends ModelWithCategory
{
    public const TABLE = 'subs';
    public const TABLE_FIELDS = ['name', 'image', 'previewImage', 'blurb', 'text', 'enableComments', 'categoryId', 'showBreadcrumbs', 'tags'];

    public string $text = '';
    public bool $enableComments = false;
    public string $tags = '';

    public function delete(): void
    {
        parent::delete();
        DBConnection::doQuery('DELETE FROM sub_backups WHERE id = ?', [$this->id]);
    }

    public function save(): bool
    {
        $oldData = null;
        if ($this->id !== null)
        {
            $oldData = self::loadFromDatabase($this->id);
        }
        $result = parent::save();
        if ($result && $oldData !== null)
        {
            DBConnection::doQuery('REPLACE INTO sub_backups(`id`, `name`, `text`) VALUES (?,?,?)', [$oldData->id, $oldData->name, $oldData->text]);
        }
        return $result;
    }

    public function hasBackup(): bool
    {
        return (bool)DBConnection::doQueryAndFetchOne('SELECT * FROM sub_backups WHERE id= ?', [$this->id]);
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

    public function getTagList(): array
    {
        if (empty($this->tags))
        {
            return [];
        }

        return explode(';', strtolower($this->tags));
    }

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
