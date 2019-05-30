<?php
namespace Cyndaron\Widget;

class YearSelector extends Widget
{
    public function __construct(int $selectedYear, bool $showEmpty = false)
    {
        $this->code = $showEmpty ? '<option value="">&nbsp;</option>' : '';
        $laatsteWaarde = ($selectedYear >= 1900) ? min($selectedYear, date("Y") - 36) : date("Y") - 36;

        for ($i = date("Y") - 5; $i >= $laatsteWaarde; $i--)
        {
            $i = (strlen($i) == 1) ? $i = "0" . $i : $i;
            $sel = ($i == $selectedYear) ? "selected" : "";
            $this->code .= "<option value='" . $i . "' $sel>" . $i . "</option>";
        }
    }
}