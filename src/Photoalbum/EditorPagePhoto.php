<?php
namespace Cyndaron\Photoalbum;

use Cyndaron\Routing\Kernel;

final class EditorPagePhoto extends \Cyndaron\Editor\EditorPage
{
    public const HAS_TITLE = false;
    public const TYPE = 'photo';
    public const TABLE = 'bijschiften';
    public const SAVE_URL = '/editor/photo/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = PhotoalbumCaption::fetchById($this->id);
            $this->content = $this->model->caption ?? '';
        }
        $_SESSION['referrer'] = Kernel::referrer();
    }
}
