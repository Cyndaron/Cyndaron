<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface WithClassesToAutowire
{
    /**
     * @return class-string[]
     */
    public function getClassesToAutowire(): array;
}
