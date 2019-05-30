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
        $tags = '';
        if ($this->id)
        {
            $sub = DBConnection::doQueryAndFetchFirstRow('SELECT * FROM subs WHERE id=?', [$this->id]);
            $enableComments = (bool)$sub['enableComments'];
            $tags = $sub['tags'];
        }

        $this->showCheckbox('enableComments', 'Reacties toestaan', $enableComments);
        ?>
        <div class="form-group row">
            <label for="tags" class="col-sm-2 col-form-label">Tags</label>
            <div class="col-sm-5">
                <input type="text" class="form-control" id="tags" name="tags" value="<?=$tags?>">
            </div>
        </div>
        <?php
    }
}