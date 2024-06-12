<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Module\TextPostProcessor;
use function preg_replace_callback;

final class CSRFTokenRenderer implements TextPostProcessor
{
    public function __construct(private readonly CSRFTokenHandler $CSRFTokenHandler)
    {
    }

    public function process(string $text): string
    {
        return preg_replace_callback('/%csrfToken\|([A-Za-z0-9_\-]+)\|([A-Za-z0-9_\-]+)%/', function($matches)
        {
            return $this->CSRFTokenHandler->get($matches[1], $matches[2]);
        }, $text) ?? $text;
    }
}
