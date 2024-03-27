<?php
namespace Cyndaron\Request;

use function array_shift;
use function count;
use function explode;
use function trim;

final class QueryBits
{
    /** @var array<int, string> */
    private array $vars;

    /**
     * @param array<int, string> $vars
     */
    public function __construct(array $vars)
    {
        $this->vars = $vars;
    }

    /**
     * @param int $index
     * @return string|null
     */
    public function get(int $index): string|null
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

    public static function fromString(string $request): self
    {
        $vars = explode('/', trim($request, '/'));
        if ($vars[0] === 'api')
        {
            array_shift($vars);
        }

        return new self($vars);
    }
}
