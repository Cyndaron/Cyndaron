<?php
declare(strict_types=1);

namespace Cyndaron\Util;

class Link
{
    public function __construct(
        public readonly string $link,
        public readonly string $name,
    ) {
    }

    /**
     * @param array{link: string, name: string} $input
     * @return self
     */
    public static function fromArray(array $input): self
    {
        return new self($input['link'], $input['name']);
    }
}
