<?php
namespace Cyndaron;

class QueryBits
{
    private array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    public function get(int $index, $fallback = null): ?string
    {
        if ($index >= count($this->vars))
            return $fallback;

        return $this->vars[$index];
    }

    public function getInt(int $index, ?int $fallback = null): ?int
    {
        $ret = $this->get($index, $fallback);
        return ($ret === null) ? null : (int)$ret;
    }
}