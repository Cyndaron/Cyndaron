<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface UrlProvider
{
    public function url(array $linkParts): ?string;
}
