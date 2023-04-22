<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale;

use Cyndaron\DBAL\Model;
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

    /** @var Concert|null  */
    protected ?Model $model = null;

    protected function prepare(): void
    {
        if ($this->id)
        {
            $this->model = Concert::fetchById($this->id);
            assert($this->model !== null);
            $this->content = $this->model->description;
            $this->contentTitle = $this->model->name;
        }

        $this->templateVars['deliveryCostInterface'] = $this->model ? $this->model->getDeliveryCostInterface() : '';
        $this->templateVars['deliveryCostOptions'] = [
            FlatFee::class => 'Vast bedrag per kaart',
            Staffel::class => 'Staffel',
        ];
        $this->templateVars['delivery'] = $this->model ? $this->model->getDelivery() : null;
        $this->templateVars['deliveryCost'] = ViewHelpers::formatCurrency($this->model->deliveryCost ?? 1.5);
        $this->templateVars['reservedSeatCharge'] = ViewHelpers::formatCurrency($this->model->reservedSeatCharge ?? 5.0);
        $this->templateVars['numFreeSeats'] = $this->model->numFreeSeats ?? 250;
        $this->templateVars['numReservedSeats'] = $this->model->numReservedSeats ?? 270;
        $this->templateVars['descriptionWhenClosed'] = $this->model->descriptionWhenClosed ?? '';
    }
}
