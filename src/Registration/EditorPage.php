<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'event';
    const TABLE = 'registration_events';
    const SAVE_URL = '/editor/event/%s';

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
            $this->twigVars['model'] = $this->model;
        }

//        $maxRegistrations = $this->model->maxRegistrations ?? 300;
//        $numSeats = $this->model->numSeats ?? 300;
        $this->twigVars['registrationCost'] = Util::formatCurrency((float)($this->model->registrationCost ?? 15.0));
    }

    protected function showContentSpecificButtons()
    {
    }
}