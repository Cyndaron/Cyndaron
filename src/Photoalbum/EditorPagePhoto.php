<?php
declare(strict_types=1);

namespace Cyndaron\Photoalbum;

final class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    public const HAS_TITLE = false;
    public const TYPE = 'photo';
    public const SAVE_URL = '/editor/photo/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = PhotoalbumCaption::fetchById($this->id);
            $this->content = $this->model?->caption ?? '';
        }

        $photoalbumId = $this->queryBits->getInt(4);
        $this->addTemplateVar('photoalbumId', $photoalbumId);
    }
}
