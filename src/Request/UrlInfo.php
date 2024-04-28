<?php
declare(strict_types=1);

namespace Cyndaron\Request;

use Symfony\Component\HttpFoundation\Request;
use function str_replace;

final class UrlInfo
{
    public function __construct(
        public readonly string $requestUri,
        public readonly string $schemeAndHost,
        public readonly string $domain,
    ) {
    }

    public static function fromRequest(Request $request): self
    {
        return new self(
            $request->getRequestUri(),
            $request->getSchemeAndHttpHost(),
            str_replace(['www.', '/'], '', $request->getHost())
        );
    }
}
