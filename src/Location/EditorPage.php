<?php
declare(strict_types=1);

namespace Cyndaron\Location;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'location';
    public const HAS_TITLE = true;

    public string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = Location::fetchById($this->id);
        }
    }
}
