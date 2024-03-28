<?php
declare(strict_types=1);

namespace Cyndaron\Page;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Error\ErrorPage;
use Symfony\Component\HttpFoundation\Response;

final class PageRenderer
{
    public function __construct()
    {
    }

    /**
     * @param array<string, mixed> $vars
     */
    public function render(Page $page, array $vars = []): string
    {
        return $page->render($vars);
    }

    /**
     * @param array<string, mixed> $vars
     * @param array<string, string> $headers
     */
    public function renderResponse(Pageable $page, array $vars = [], int $status = 200, array $headers = []): Response
    {
        return new Response($this->render($page->toPage(), $vars), $status, $headers);
    }

    public function renderErrorResponse(ErrorPage $errorPage): Response
    {
        return $this->renderResponse($errorPage, status: $errorPage->status, headers: $errorPage->headers);
    }
}
