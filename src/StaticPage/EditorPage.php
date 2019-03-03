<?php
namespace Cyndaron\StaticPage;

use Cyndaron\DBConnection;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    protected $type = 'sub';
    protected $table = 'subs';
    protected $saveUrl = '/editor/sub/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->content = DBConnection::doQueryAndFetchOne('SELECT tekst FROM ' . $this->vvstring . 'subs WHERE id=?', [$this->id]);
            $this->contentTitle = DBConnection::doQueryAndFetchOne('SELECT naam FROM ' . $this->vvstring . 'subs WHERE id=?', [$this->id]);
        }
    }

    protected function showContentSpecificButtons()
    {
        $checked = false;
        if ($this->id)
            $checked = (bool)DBConnection::doQueryAndFetchOne('SELECT reacties_aan FROM subs WHERE id=?', [$this->id]);
        $this->showCheckbox('reacties_aan', 'Reacties toestaan', $checked);

        $this->showCategoryDropdown();
    }
}