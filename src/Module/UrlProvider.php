<?php
declare(strict_types=1);

namespace Cyndaron\Module;

use Cyndaron\DBAL\GenericRepository;

interface UrlProvider
{
    /**
     * @param GenericRepository $genericRepository
     * @param string[] $linkParts
     * @return string|null
     */
    public function nameFromUrl(GenericRepository $genericRepository, array $linkParts): string|null;
}
