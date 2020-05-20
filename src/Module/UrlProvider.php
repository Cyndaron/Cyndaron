<?php
namespace Cyndaron\Module;

interface UrlProvider
{
    public function url(array $linkParts): ?string;
}
