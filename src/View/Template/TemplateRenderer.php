<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

class TemplateRenderer
{
    public function __construct(private readonly ViewFactory $factory)
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
}
