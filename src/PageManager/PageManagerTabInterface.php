<?php
declare(strict_types=1);

namespace Cyndaron\PageManager;

use Cyndaron\Request\QueryBits;

interface PageManagerTabInterface
{
    public function render(QueryBits $queryBits): string;
}
