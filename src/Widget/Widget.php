<?php
namespace Cyndaron\Widget;

class Widget
{
    protected string $code;

    public function __toString()
    {
        if (!empty($this->code))
        {
            return $this->code;
        }

        return '';
    }

    /**
     * @return static
     */
    public static function create(): Widget
    {
        $arguments = func_get_args();
        $class = static::class;
        return new $class(...$arguments);
    }
}