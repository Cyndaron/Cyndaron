<?php
declare (strict_types = 1);

namespace Cyndaron\Kaartverkoop;

class EditorPage extends \Cyndaron\EditorPage
{
    protected $type = 'concert';
    protected $table = 'kaartverkoop_concerten';
    protected $saveUrl = '/editor/concert/%s';

    protected function prepare()
    {
        if ($this->id)
        {
            $this->record = Concert::loadFromDatabase((int)$this->id)->asArray();
            $this->content = $this->record['beschrijving'];
            $this->contentTitle = $this->record['naam'];
        }
    }

    protected function showContentSpecificButtons()
    {
        $deliveryFee = Util::formatCurrency((float)($this->record['verzendkosten'] ?? 1.5));
        $reservedSeatFee = Util::formatCurrency((float)($this->record['toeslag_gereserveerde_plaats'] ?? 5.0));
        $closedDescription = $this->record['beschrijving_indien_gesloten'] ?? '';
        ?>
        <div class="form-group">
            <label for="closedDescription">Beschijving indien gesloten:</label>
            <textarea class="form-control" id="closedDescription" name="closedDescription" rows="3"><?=$closedDescription?></textarea>
        </div>
        <?php
        $this->showCheckbox('salesOpen', 'Open voor verkoop', (bool)($this->record['open_voor_verkoop'] ?? false));
        $this->showCheckbox('forcedDelivery', 'Bezorgen verplicht', (bool)($this->record['bezorgen_verplicht'] ?? false));
        $this->showCheckbox('hasReservedSeats', 'Heeft gereserveerde plaatsen', (bool)($this->record['heeft_gereserveerde_plaatsen'] ?? false));
        $this->showCheckbox('reservedSeatsSoldOut', 'Gereserveerde plaatsen zijn uitverkocht', (bool)($this->record['gereserveerde_plaatsen_uitverkocht'] ?? false));
        ?>
        <div class="form-group row">
            <label for="deliveryFee" class="col-sm-2 col-form-label">Verzendkosten</label>
            <div class="input-group col-sm-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">€</span>
                </div>
                <input type="text" class="form-control" id="deliveryFee" name="deliveryFee" value="<?=$deliveryFee?>">
            </div>
        </div>
        <div class="form-group row">
            <label for="reservedSeatFee" class="col-sm-2 col-form-label">Toeslag gereserveerde plaats</label>
            <div class="input-group col-sm-1">
                <div class="input-group-prepend">
                    <span class="input-group-text">€</span>
                </div>
                <input type="text" class="form-control" id="reservedSeatFee" name="reservedSeatFee" value="<?=$reservedSeatFee?>">
            </div>
        </div>
        <?php
    }
}