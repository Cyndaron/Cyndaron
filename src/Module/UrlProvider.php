<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\DBAL\Model;
use Cyndaron\Url\Url;

interface UrlProvider
{
    /**
     * @param string[] $linkParts
     * @return string|null
     */
    public function nameFromUrl(array $linkParts): string|null;
}
