<?php
/**
 * Copyright © 2009-2020 Michael Steenbeek
 *
 * Cyndaron is licensed under the MIT License. See the LICENSE file for more details.
 */

declare(strict_types=1);

namespace Cyndaron\Error;

use Cyndaron\Page\Page;
use Cyndaron\Page\Pageable;
use Cyndaron\Page\SimplePage;
use Symfony\Component\HttpFoundation\Response;

/**
 * Makes it easy to return a simple error page from a controller method.
 */
class ErrorPage implements Pageable
{
    private readonly SimplePage $page;
    public readonly int $status;
    /** @var array<string, string> */
    public readonly array $headers;

    /**
     * @param string $title
     * @param string $body
     * @param int $status A HTTP status code that is appropriate for the error.
     * @param array<string, string> $headers Extra HTTP headers (optional)
     */
    public function __construct(
        string $title,
        string $body,
        int $status = Response::HTTP_INTERNAL_SERVER_ERROR,
        array $headers = []
    ) {
        $this->page = new SimplePage($title, $body);
        $this->status = $status;
        $this->headers = $headers;
    }

    public function toPage(): Page
    {
        return $this->page->toPage();
    }
}
