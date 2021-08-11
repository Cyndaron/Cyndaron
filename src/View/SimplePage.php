<?php
declare(strict_types=1);

namespace Cyndaron\View;

/**
 * A simple page class, useful for pages with just a title and a few sentences of text.
 */
final class SimplePage
{
    private Page $page;

    public function __construct(string $title, string $body)
    {
        $this->page = new Page($title, $body);
    }

    public function render(array $vars = []): string
    {
        return $this->page->render($vars);
    }
}
