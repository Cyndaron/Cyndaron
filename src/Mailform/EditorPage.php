<?php
declare(strict_types=1);

namespace Cyndaron\Mailform;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'mailform';
    public const TABLE = 'mailformulieren';
    public const SAVE_URL = '/editor/mailform/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = new Mailform((int)$this->id);
            $this->model->load();
            $this->content = $this->model->confirmationText ?? '';
            $this->contentTitle = $this->model->name;
        }
    }
}
