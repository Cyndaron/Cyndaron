<?php
declare (strict_types = 1);

namespace Cyndaron\Ticketsale;

class EditorPage extends \Cyndaron\Editor\EditorPage
{
    const TYPE = 'concert';
    const TABLE = 'ticketsale_concerts';
    const SAVE_URL = '/editor/concert/%s';

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
    }

    protected function showContentSpecificButtons()
    {
        $deliveryCost = Util::formatCurrency((float)($this->model->deliveryCost ?? 1.5));
        $reservedSeatCharge = Util::formatCurrency((float)($this->model->reservedSeatCharge ?? 5.0));
        $descriptionWhenClosed = $this->model->descriptionWhenClosed ?? '';
        ?>
        <div class="form-group">
            <label for="descriptionWhenClosed">Beschijving indien gesloten:</label>
            <textarea class="form-control" id="descriptionWhenClosed" name="descriptionWhenClosed" rows="3"><?=$descriptionWhenClosed?></textarea>
        </div>
        <?php
        $this->showCheckbox('openForSales', 'Open voor verkoop', (bool)($this->model->openForSales ?? false));
        $this->showCheckbox('forcedDelivery', 'Bezorgen verplicht', (bool)($this->model->forcedDelivery ?? false));
        $this->showCheckbox('hasReservedSeats', 'Heeft gereserveerde plaatsen', (bool)($this->model->hasReservedSeats ?? false));
        $this->showCheckbox('reservedSeatsAreSoldOut', 'Gereserveerde plaatsen zijn uitverkocht', (bool)($this->model->reservedSeatsAreSoldOut ?? false));
        ?>
        <div class="form-group row">
            <label for="deliveryCost" class="col-sm-2 col-form-label">Verzendkosten</label>
            <div class="input-group col-sm-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">€</span>
                </div>
                <input type="text" class="form-control" id="deliveryCost" name="deliveryCost" value="<?=$deliveryCost?>">
            </div>
        </div>
        <div class="form-group row">
            <label for="reservedSeatCharge" class="col-sm-2 col-form-label">Toeslag gereserveerde plaats</label>
            <div class="input-group col-sm-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">€</span>
                </div>
                <input type="text" class="form-control" id="reservedSeatCharge" name="reservedSeatCharge" value="<?=$reservedSeatCharge?>">
            </div>
        </div>
        <?php
    }
}