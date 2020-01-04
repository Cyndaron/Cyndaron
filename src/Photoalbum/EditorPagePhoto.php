<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;

class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    const HAS_TITLE = false;
    const TYPE = 'photo';
    const TABLE = 'bijschiften';
    const SAVE_URL = '/editor/photo/%s';

    protected $template = '';

    protected $hash;

    protected function prepare()
    {
        $this->hash = Request::getVar(3);
        if ($this->id)
        {
            $this->model = PhotoalbumCaption::loadFromDatabase($this->id);
            $this->content = $this->model->caption;
        }
        $_SESSION['referrer'] = Request::referrer();
        $this->templateVars['hash'] = $this->hash;
    }
}
