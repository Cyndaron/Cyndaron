<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Illuminate\View\Factory;

class TemplateRenderer
{
    public function __construct(private readonly Factory $factory, private readonly TemplateFinder $templateFinder)
    {
    }

    /**
     * @param string $template
     * @param array<string, mixed> $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        $result = $this->factory->make($template, $data);
        return $result->render();
    }

    public function templateExists(string $name): bool
    {
        return $this->templateFinder->path($name) !== null;
    }
}
