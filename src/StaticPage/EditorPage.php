<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'sub';
    const TABLE = 'subs';
    const SAVE_URL = '/editor/sub/%s';
    const HAS_CATEGORY = true;
    protected $template = '';

    protected function prepare()
    {
        $enableComments = false;
        $tags = '';
        if ($this->id)
        {
            $table = ($this->vvstring) ? 'sub_backups' : self::TABLE;
            $this->content = DBConnection::doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);

            $sub = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM subs WHERE id=?', [$this->id]);
            $enableComments = (bool)$sub['enableComments'];
            $tags = $sub['tags'];
        }

        $this->templateVars['enableComments'] = $enableComments;
        $this->templateVars['tags'] = $tags;
    }

    protected function showContentSpecificButtons()
    {
    }
}