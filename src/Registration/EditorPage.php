<?php
declare(strict_types=1);

namespace Cyndaron\Registration;

use Cyndaron\View\Template\ViewHelpers;
use function assert;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'event';
    public const SAVE_URL = '/editor/event/%s';

    public string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = Event::fetchById($this->id);
            assert($this->model !== null);
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
        $this->templateVars['registrationCost0'] = ViewHelpers::formatCurrency($this->model->registrationCost0 ?? 15.0);
        $this->templateVars['registrationCost1'] = ViewHelpers::formatCurrency($this->model->registrationCost1 ?? 15.0);
        $this->templateVars['registrationCost2'] = ViewHelpers::formatCurrency($this->model->registrationCost2 ?? 0.0);
        $this->templateVars['registrationCost3'] = ViewHelpers::formatCurrency($this->model->registrationCost3 ?? 0.0);
        $this->templateVars['lunchCost'] = ViewHelpers::formatCurrency((float)($this->model->lunchCost ?? 15.0));
    }
}
