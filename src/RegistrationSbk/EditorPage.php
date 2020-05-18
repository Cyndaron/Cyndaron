<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'eventSbk';
    public const TABLE = 'registrationsbk_events';
    public const SAVE_URL = '/editor/eventSbk/%s';

    protected string $template = '';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->model = new Event($this->id);
            $this->model->load();
            $this->content = $this->model->description;
            $this->contentTitle = $this->model->name;
            $this->templateVars['model'] = $this->model;
        }

        $this->templateVars['registrationCost'] = Util::formatCurrency((float)($this->model->registrationCost ?? 65.0));
    }
}