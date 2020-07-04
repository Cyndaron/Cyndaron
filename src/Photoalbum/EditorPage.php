<?php
namespace Cyndaron\Photoalbum;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'photoalbum';
    public const TABLE = 'photoalbums';
    public const HAS_CATEGORY = true;
    public const SAVE_URL = '/editor/photoalbum/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $photoalbum = Photoalbum::loadFromDatabase($this->id);
            if ($photoalbum !== null)
            {
                $this->model = $photoalbum;
                $this->content = $photoalbum->notes;
                $this->contentTitle = $photoalbum->name;
            }
        }
        $this->templateVars['viewModeOptions'] = Photoalbum::VIEWMODE_DESCRIPTIONS;
    }
}
