<?php
declare (strict_types = 1);

namespace Cyndaron\RegistrationSbk;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'eventSbk';
    const TABLE = 'registrationsbk_events';
    const SAVE_URL = '/editor/eventSbk/%s';

    protected $template = '';

    /** @var Event|null */
    protected $model = null;

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