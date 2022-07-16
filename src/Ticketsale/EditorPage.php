<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\Ticketsale\DeliveryCost\FlatFee;
use Cyndaron\Ticketsale\DeliveryCost\Staffel;
use Cyndaron\View\Template\ViewHelpers;
use function assert;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'concert';
    public const TABLE = 'ticketsale_concerts';
    public const SAVE_URL = '/editor/concert/%s';

    protected string $template = '';

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = new Concert($this->id);
            $this->model->load();
            $this->content = $this->model->description;
            $this->contentTitle = $this->model->name;
        }

        assert($this->model instanceof Concert);

        $this->templateVars['deliveryCostInterface'] = $this->model->getDeliveryCostInterface();
        $this->templateVars['deliveryCostOptions'] = [
            '\Cyndaron\Ticketsale\DeliveryCost\FlatFee' => 'Vast bedrag per kaart',
            '\Cyndaron\Ticketsale\DeliveryCost\Staffel' => 'Staffel',
        ];
        $this->templateVars['delivery'] = $this->model->getDelivery();
        $this->templateVars['deliveryCost'] = ViewHelpers::formatCurrency($this->model->deliveryCost ?? 1.5);
        $this->templateVars['reservedSeatCharge'] = ViewHelpers::formatCurrency($this->model->reservedSeatCharge ?? 5.0);
        $this->templateVars['numFreeSeats'] = $this->model->numFreeSeats ?? 250;
        $this->templateVars['numReservedSeats'] = $this->model->numReservedSeats ?? 270;
        $this->templateVars['descriptionWhenClosed'] = $this->model->descriptionWhenClosed ?? '';
    }
}
