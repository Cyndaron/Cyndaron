<?php
namespace Cyndaron\Widget;

use Cyndaron\Util;

class MonthSelector extends Widget
{
    public function __construct(int $selectedMonth, bool $showEmpty = false)
    {
        $this->code = $showEmpty ? '<option value="">&nbsp;</option>' : '';

        for ($i = 1; $i <= 12; $i++)
        {
            $label = Util::getMonth($i);
            $i = (strlen($i) == 1) ? $i = "0" . $i : $i;
            $sel = ($i == $selectedMonth) ? "selected" : "";
            $this->code .= "<option value='" . $i . "' $sel>" . $label . "</option>";
        }
    }
}