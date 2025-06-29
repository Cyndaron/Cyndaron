<?php
declare(strict_types=1);

namespace Cyndaron\Ticketsale\Concert;

use Cyndaron\DBAL\Model;
use Cyndaron\DBAL\Repository\GenericRepository;
use Cyndaron\Location\Location;
use Cyndaron\Ticketsale\DeliveryCost\FlatFee;
use Cyndaron\Ticketsale\DeliveryCost\Staffel;
use Cyndaron\View\Template\ViewHelpers;
use function asort;
use function assert;

final class EditorPage extends \Cyndaron\Editor\EditorPage
{
    public const TYPE = 'concert';
    public const TABLE = 'ticketsale_concerts';
    public const SAVE_URL = '/editor/concert/%s';

    public string $template = '';

    public Model|null $model = null;

    public function __construct(
        private readonly ConcertRepository $concertRepository,
        private readonly GenericRepository $genericRepository,
    ) {

    }

    public function prepare(): void
    {
        if ($this->id)
        {
            $this->model = $this->concertRepository->fetchById($this->id);
            assert($this->model !== null);
            $this->content = $this->model->description;
            $this->contentTitle = $this->model->name;
        }

        $locations = [];
        foreach ($this->genericRepository->fetchAll(Location::class) as $location)
        {
            $locations[$location->id] = $location->getName();
        }
        asort($locations);

        $this->templateVars['deliveryCostInterface'] = $this->model instanceof Concert ? $this->model->getDeliveryCostInterface() : '';
        $this->templateVars['deliveryCostOptions'] = [
            FlatFee::class => 'Vast bedrag per kaart',
            Staffel::class => 'Staffel',
        ];
        $this->templateVars['delivery'] = $this->model instanceof Concert ? $this->model->getDelivery()->value : TicketDelivery::DIGITAL;
        $this->templateVars['deliveryCost'] = ViewHelpers::formatCurrency($this->model->deliveryCost ?? 1.5);
        $this->templateVars['reservedSeatCharge'] = ViewHelpers::formatCurrency($this->model->reservedSeatCharge ?? 5.0);
        $this->templateVars['numFreeSeats'] = $this->model->numFreeSeats ?? 250;
        $this->templateVars['numReservedSeats'] = $this->model->numReservedSeats ?? 270;
        $this->templateVars['descriptionWhenClosed'] = $this->model->descriptionWhenClosed ?? '';
        $this->templateVars['locations'] = $locations;
    }
}
