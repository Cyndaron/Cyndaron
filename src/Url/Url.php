<?php
declare(strict_types=1);

namespace Cyndaron\Url;

use Stringable;

final class Url implements Stringable
{
    private string $url;

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function __toString(): string
    {
        return $this->url;
    }
}
