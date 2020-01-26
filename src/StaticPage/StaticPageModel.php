<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Model;
use Cyndaron\Url;
use Cyndaron\Util;
use Exception;

class StaticPageModel extends Model
{
    const TABLE = 'subs';
    const TABLE_FIELDS = ['name', 'text', 'enableComments', 'categoryId', 'showBreadcrumbs', 'tags'];
    const HAS_CATEGORY = true;

    public string $name = '';
    public string $text = '';
    public bool $enableComments = false;
    public ?int $categoryId = null;
    public bool $showBreadcrumbs = false;
    public string $tags = '';

    public function delete(): void
    {
        parent::delete();
        DBConnection::doQuery('DELETE FROM sub_backups WHERE id = ?', [$this->id]);
    }

    public function save(): bool
    {
        $oldData = self::loadFromDatabase($this->id);
        $result = parent::save();
        if ($result)
        {
            DBConnection::doQuery('REPLACE INTO sub_backups(`id`, `name`, `text`) VALUES (?,?,?)', [$oldData->id, $oldData->name, $oldData->text]);
        }
        return $result;
    }

    public function hasBackup(): bool
    {
        return (bool)DBConnection::doQueryAndFetchOne('SELECT * FROM sub_backups WHERE id= ?', [$this->id]);
    }

    public function react(string $author, string $reactie, string $antispam)
    {
        if ($this->id == null)
        {
            throw new Exception('No ID!');
        }
        if ($this->load() && $this->enableComments)
        {
            if ($author && $reactie && ($antispam == 'acht' || $antispam == '8'))
            {
                $prep = DBConnection::getPdo()->prepare('INSERT INTO sub_replies(subId, author, text) VALUES (?, ?, ?)');
                $prep->execute([$this->id, $author, $reactie]);
                return true;
            }
        }
        return false;
    }

    public function getTagList(): array
    {
        if (empty($this->tags))
            return [];

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

    public function getBlurb(): string
    {
        return html_entity_decode(Util::wordlimit(trim($this->text), 30));
    }

    public function getImage(): string
    {
        preg_match("/<img.*?src=\"(.*?)\".*?>/si", $this->text, $match);
        return $page['image'] = $match[1] ?? '';
    }
}