<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'sub';
    public const TABLE = 'subs';
    public const SAVE_URL = '/editor/sub/%s';
    public const HAS_CATEGORY = true;

    protected string $template = '';

    protected function prepare()
    {
        $enableComments = false;
        $tags = '';
        if ($this->id)
        {
            $table = ($this->vvstring) ? 'sub_backups' : self::TABLE;
            /** @noinspection SqlResolve */
            $this->content = DBConnection::doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            /** @noinspection SqlResolve */
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);

            $sub = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM subs WHERE id=?', [$this->id]);
            $enableComments = (bool)$sub['enableComments'];
            $tags = $sub['tags'];
        }

        $this->templateVars['enableComments'] = $enableComments;
        $this->templateVars['tags'] = $tags;
    }
}