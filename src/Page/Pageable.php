<?php
declare(strict_types=1);

namespace Cyndaron\Page;

interface Pageable
{
    public function toPage(): Page;
}
