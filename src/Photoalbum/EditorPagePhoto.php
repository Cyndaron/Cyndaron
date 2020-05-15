<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Request;

class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    const HAS_TITLE = false;
    const TYPE = 'photo';
    const TABLE = 'bijschiften';
    const SAVE_URL = '/editor/photo/%s';

    protected string $template = '';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->model = PhotoalbumCaption::loadFromDatabase($this->id);
            $this->content = $this->model->caption;
        }
        $_SESSION['referrer'] = Request::referrer();
    }
}
