<?php
declare(strict_types=1);

namespace Cyndaron\User;

use Cyndaron\Module\TextPostProcessor;
use function preg_replace_callback;

final class CSRFTokenRenderer implements TextPostProcessor
{
    public function process(string $text): string
    {
        return preg_replace_callback('/%csrfToken\|([A-Za-z0-9_\-]+)\|([A-Za-z0-9_\-]+)%/', static function($matches)
        {
            return User::getCSRFToken($matches[1], $matches[2]);
        }, $text) ?? $text;
    }
}
