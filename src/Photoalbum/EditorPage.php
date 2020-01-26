<?php
namespace Cyndaron\Photoalbum;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'photoalbum';
    const TABLE = 'photoalbums';
    const HAS_CATEGORY = true;
    const SAVE_URL = '/editor/photoalbum/%s';

    protected string $template = '';

    protected function prepare()
    {
        if ($this->id)
        {
            $photoalbum = Photoalbum::loadFromDatabase($this->id);
            $this->model = $photoalbum;
            $this->content = $photoalbum->notes;
            $this->contentTitle = $photoalbum->name;
        }
        $this->templateVars['viewModeOptions'] = Photoalbum::VIEWMODE_DESCRIPTIONS;
    }
}