<?php
declare(strict_types=1);

namespace Cyndaron\Gopher\Response;

class PlainTextResponse implements ResponseInterface
{
    use SendNonStreamTrait;

    public function __construct(private readonly string $contents)
    {
    }

    public function encode(): string
    {
        return $this->contents;
    }
}
