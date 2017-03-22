<?php
namespace Cyndaron\Widget;

class JaarSelector extends Widget
{
    public function __construct(int $geselecteerdJaar, bool $toonLegeOptie = false)
    {
        $this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';
        $laatsteWaarde = ($geselecteerdJaar >= 1900) ? min($geselecteerdJaar, date("Y") - 36) : date("Y") - 36;

        for ($i = date("Y") - 5; $i >= $laatsteWaarde; $i--)
        {
            $i = (strlen($i) == 1) ? $i = "0" . $i : $i;
            $sel = ($i == $geselecteerdJaar) ? "selected" : "";
            $this->code .= "<option value='" . $i . "' $sel>" . $i . "</option>";
        }
    }
}