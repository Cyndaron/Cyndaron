<?php
namespace Cyndaron\Widget;

use Cyndaron\Util;

class MaandSelector extends Widget
{
    public function __construct(int $geselecteerdeMaand, bool $toonLegeOptie = false)
    {
        $this->code = $toonLegeOptie ? '<option value="">&nbsp;</option>' : '';

        for ($i = 1; $i <= 12; $i++)
        {
            $label = Util::geefMaand($i);
            $i = (strlen($i) == 1) ? $i = "0" . $i : $i;
            $sel = ($i == $geselecteerdeMaand) ? "selected" : "";
            $this->code .= "<option value='" . $i . "' $sel>" . $label . "</option>";
        }
    }
}