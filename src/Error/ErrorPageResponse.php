<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\View\Page;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes it easy to return a simple error page from a controller method.
 */
class ErrorPageResponse extends Response
{
    /**
     * ErrorPageResponse constructor.
     * @param string $title
     * @param string $body
     * @param int $status A HTTP status code that is appropriate for the error.
     * @param array $headers Extra HTTP headers (optional)
     */
    public function __construct(string $title, string $body, int $status = Response::HTTP_INTERNAL_SERVER_ERROR, array $headers = [])
    {
        $page = new Page($title, $body);
        parent::__construct($page->render(), $status, $headers);
    }
}
