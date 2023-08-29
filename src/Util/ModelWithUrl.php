<?php
declare(strict_types=1);

namespace Cyndaron\Util;

interface ModelWithUrl
{
    public function getUrl(): string;
}
