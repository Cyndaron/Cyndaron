<?php
declare(strict_types=1);

namespace Cyndaron\Gopher;

class MenuEntry
{
    public function __construct(public EntryType $type, public string $title, public string $location, public string $server, public int $port)
    {
    }

    public function encode(): string
    {
        return $this->type->value . $this->title . "\t" . $this->location . "\t" . $this->server . "\t" . $this->port . "\r\n";
    }
}
