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
     * @param mixed|null $fallback
     * @return string|null
     */
    public function get(int $index, $fallback = null): ?string
    {
        if (!$this->hasIndex($index))
        {
            return $fallback;
        }

        return $this->vars[$index];
    }

    public function getInt(int $index, int $fallback = 0): int
    {
        $ret = $this->get($index, $fallback);
        return ($ret === null) ? $fallback : (int)$ret;
    }

    public function getNullableInt(int $index, ?int $fallback = null): ?int
    {
        $ret = $this->get($index, $fallback);
        return ($ret === null) ? null : (int)$ret;
    }

    public function getString(int $index, string $fallback = ''): string
    {
        $ret = $this->get($index, $fallback);
        return $ret ?? $fallback;
    }

    public function hasIndex(int $index): bool
    {
        return $index >= 0 && $index < count($this->vars);
    }
}
