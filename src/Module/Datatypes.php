<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface Datatypes
{
    /**
     * @return array<string, Datatype>
     */
    public function dataTypes(): array;
}
