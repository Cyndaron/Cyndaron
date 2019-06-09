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

    public static function create()
    {
        $arguments = func_get_args();
        $class = get_called_class();
        return new $class(...$arguments);
    }
}