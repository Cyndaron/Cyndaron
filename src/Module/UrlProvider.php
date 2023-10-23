<?php
declare(strict_types=1);

namespace Cyndaron\Module;

interface UrlProvider
{
    /**
     * @param string[] $linkParts
     * @return string|null
     */
    public function url(array $linkParts): string|null;
}
