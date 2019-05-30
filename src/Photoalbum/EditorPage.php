<?php
namespace Cyndaron\Photoalbum;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'photoalbum';
    const TABLE = 'photoalbums';
    const HAS_CATEGORY = true;

    const SAVE_URL = '/editor/photoalbum/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            /** @var Photoalbum $photoalbum */
            $photoalbum = Photoalbum::loadFromDatabase($this->id);
            $this->content = $photoalbum->notes;
            $this->contentTitle = $photoalbum->name;
        }
    }

    // Not used, but required.
    protected function showContentSpecificButtons() {}
}