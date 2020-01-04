<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'concert';
    const TABLE = 'ticketsale_concerts';
    const SAVE_URL = '/editor/concert/%s';
    protected $template = '';

    /** @var Concert|null */
    protected $model = null;

    protected function prepare()
    {
        if ($this->id)
        {
            $this->model = new Concert($this->id);
            $this->model->load();
            $this->content = $this->model->description;
            $this->contentTitle = $this->model->name;
        }

        $this->templateVars['deliveryCost'] = Util::formatCurrency((float)($this->model->deliveryCost ?? 1.5));
        $this->templateVars['reservedSeatCharge'] = Util::formatCurrency((float)($this->model->reservedSeatCharge ?? 5.0));
        $this->templateVars['numFreeSeats'] = $this->model->numFreeSeats ?? 250;
        $this->templateVars['numReservedSeats'] = $this->model->numReservedSeats ?? 270;
        $this->templateVars['descriptionWhenClosed'] = $this->model->descriptionWhenClosed ?? '';
    }

}