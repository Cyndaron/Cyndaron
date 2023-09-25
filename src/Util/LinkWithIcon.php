<?php
declare(strict_types=1);

namespace Cyndaron\Util;

final class LinkWithIcon extends Link
{
    public function __construct(
        string $link,
        string $name,
        public readonly string $icon,
    ) {
        parent::__construct($link, $name);
    }

    /**
     *
     * @param array{link: string, name: string, icon: string} $input
     * @return self
     * @phpstan-ignore-next-line
     */
    public static function fromArray(array $input): self
    {
        return new self($input['link'], $input['name'], $input['icon']);
    }
}
