<?php
namespace Cyndaron\Widget;

class Widget
{
    protected $code;

    public function __toString()
    {
        if (!empty($this->code))
        {
            return $this->code;
        }

        return '';
    }
}