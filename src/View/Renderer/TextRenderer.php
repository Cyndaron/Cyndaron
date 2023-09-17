<?php
declare(strict_types=1);

namespace Cyndaron\View\Renderer;

use Cyndaron\Module\TextPostProcessor;

final class TextRenderer
{
    /**
     * @var TextPostProcessor[]
     */
    private static array $textPostProcessors = [];

    public static function addTextPostProcessor(TextPostProcessor $postProcessor): void
    {
        self::$textPostProcessors[] = $postProcessor;
    }

    public function __construct(public readonly string $original)
    {
    }

    public function render(): string
    {
        $rendered = $this->original;
        foreach (self::$textPostProcessors as $postProcessor)
        {
            $rendered = $postProcessor->process($rendered);
        }

        return $rendered;
    }
}
