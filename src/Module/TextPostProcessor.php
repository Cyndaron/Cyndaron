<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface TextPostProcessor
{
    public function process(string $text): string;
}
