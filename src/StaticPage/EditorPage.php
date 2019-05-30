<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'sub';
    const TABLE = 'subs';
    const SAVE_URL = '/editor/sub/%s';
    const HAS_CATEGORY = true;

    protected function prepare()
    {
        if ($this->id)
        {
            $table = ($this->vvstring) ? 'sub_backups' : self::TABLE;
            $this->content = DBConnection::doQueryAndFetchOne('SELECT text FROM ' . $table . ' WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT name FROM ' . $table . ' WHERE id=?', [$this->id]);
        }
    }

    protected function showContentSpecificButtons()
    {
        $enableComments = false;
        if ($this->id)
        {
            $enableComments = (bool)DBConnection::doQueryAndFetchOne('SELECT enableComments FROM subs WHERE id=?', [$this->id]);
        }

        $this->showCheckbox('enableComments', 'Reacties toestaan', $enableComments);
    }
}