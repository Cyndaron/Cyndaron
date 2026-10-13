<?php
/**
 * Copyright © 2009-2025 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

declare(strict_types=1);

namespace Cyndaron\Error;

use Symfony\Component\HttpFoundation\Response;

/**
 * Makes it easy to return a simple error page from a controller method.
 */
final class ErrorPage
{
    /**
     * @param string $title
     * @param string $body
     * @param int $status A HTTP status code that is appropriate for the error.
     * @param array<string, string> $headers Extra HTTP headers (optional)
     */
    public function __construct(
        public readonly string $title,
        public readonly string $body,
        public readonly int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        public readonly array $headers = []
    ) {
    }
}
