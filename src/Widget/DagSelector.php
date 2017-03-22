<?php
namespace Cyndaron\Widget;

class DagSelector extends Widget
{
    public function __construct(int $geselecteerdeDag, bool $toonLegeOptie = false)
    {
        $this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';

        for ($i = 1; $i <= 31; $i++)
        {
            $i = (strlen($i) == 1) ? $i = "0" . $i : $i;
            $sel = ($i == $geselecteerdeDag) ? "selected" : "";
            $this->code .= "<option value='" . $i . "' $sel>" . $i . "</option>";
        }
    }
}