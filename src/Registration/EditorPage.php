<?php
declare (strict_types = 1);

namespace Cyndaron\Registration;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'event';
    const TABLE = 'registration_events';
    const SAVE_URL = '/editor/event/%s';

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
        else
        {
            $this->model = new Event();
        }

//        $maxRegistrations = $this->model->maxRegistrations ?? 300;
//        $numSeats = $this->model->numSeats ?? 300;
        $this->templateVars['registrationCost0'] = Util::formatCurrency((float)($this->model->registrationCost0 ?? 15.0));
        $this->templateVars['registrationCost1'] = Util::formatCurrency((float)($this->model->registrationCost1 ?? 15.0));
        $this->templateVars['lunchCost'] = Util::formatCurrency((float)($this->model->lunchCost ?? 15.0));
    }
}