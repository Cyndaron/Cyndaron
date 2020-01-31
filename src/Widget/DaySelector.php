<?php
namespace Cyndaron\Widget;

class DaySelector extends Widget
{
    public function __construct(int $selectedDay, bool $showEmpty = false)
    {
        $this->code = $showEmpty ? '<option value="">&nbsp;</option>' : '';

        for ($i = 1; $i <= 31; $i++)
        {
            $i = (strlen($i) === 1) ? $i = '0' . $i : $i;
            $sel = ($i === $selectedDay) ? 'selected' : '';
            $this->code .= sprintf('<option value="%d" %s>%d</option>', $i, $sel, $i);
        }
    }
}