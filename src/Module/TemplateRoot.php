<?php
declare(strict_types=1);

namespace Cyndaron\Module;

final class TemplateRoot
{
    public string $name;
    public string $root;

    public function __construct(string $name, string $root)
    {
        $this->name = $name;
        $this->root = $root;
    }
}
