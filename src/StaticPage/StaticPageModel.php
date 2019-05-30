<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;
use Cyndaron\Model;

class StaticPageModel extends Model
{
    const TABLE = 'subs';
    const TABLE_FIELDS = ['name', 'text', 'enableComments', 'categoryId', 'showBreadcrumbs'];
    const HAS_CATEGORY = true;

    public $name = '';
    public $text = '';
    public $enableComments = false;
    public $categoryId = null;
    public $showBreadcrumbs = false;

    public function delete(): void
    {
        parent::delete();
        DBConnection::doQuery('DELETE FROM sub_backups WHERE id = ?', [$this->id]);
    }

    public function hasBackup(): bool
    {
        return (bool)DBConnection::doQueryAndFetchOne('SELECT * FROM sub_backups WHERE id= ?', [$this->id]);
    }

    public function react(string $author, string $reactie, string $antispam)
    {
        if ($this->id == null)
        {
            throw new \Exception('No ID!');
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
}