<?php
namespace Cyndaron\Request;

use function count;

final class QueryBits
{
    private array $vars;

    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function get(int $index): mixed
    {
        if (!$this->hasIndex($index))
        {
            return null;
        }

        return $this->vars[$index];
    }

    public function getInt(int $index, int $fallback = 0): int
    {
        $ret = $this->get($index);
        return ($ret === null) ? $fallback : (int)$ret;
    }

    public function getNullableInt(int $index, int|null $fallback = null): int|null
    {
        $ret = $this->get($index);
        return ($ret === null) ? $fallback : (int)$ret;
    }

    public function getString(int $index, string $fallback = ''): string
    {
        $ret = $this->get($index);
        return ($ret === null) ? $fallback : (string)$ret;
    }

    public function hasIndex(int $index): bool
    {
        return $index >= 0 && $index < count($this->vars);
    }
}
