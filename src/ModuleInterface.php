<?php
declare(strict_types=1);

namespace Cyndaron;

interface ModuleInterface
{
    public function routes(): array;

    public function dataTypes(): array;
}