<?php
declare (strict_types = 1);

namespace Cyndaron\Mailform;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'mailform';
    const TABLE = 'mailformulieren';
    const SAVE_URL = '/editor/mailform/%s';

    protected $template = '';

    /** @var Mailform|null  */
    protected $model = null;

    protected function prepare()
    {
        if ($this->id)
        {
            $this->model = new Mailform((int)$this->id);
            $this->model->load();
            $this->content = $this->model->confirmationText;
            $this->contentTitle = $this->model->name;
        }
    }
}