<?php
declare(strict_types=1);

namespace Cyndaron\Module;

final class InternalLink
{
    public function __construct(
        public readonly string $name,
        public readonly string $link,
    )
    {
    }

    /**
     * @param array{name: string, link: string} $input
     * @return self
     */
    public static function fromArray(array $input): self
    {
        return new self($input['name'], $input['link']);
    }
}
