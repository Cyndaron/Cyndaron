<?php
declare(strict_types=1);

namespace Cyndaron\View\Renderer;

use Cyndaron\Base\ModuleRegistry;
use Cyndaron\Module\TextPostProcessor;
use Cyndaron\Util\DependencyInjectionContainer;
use Psr\Log\LoggerInterface;

final class TextRenderer
{
    /**
     * @var TextPostProcessor[]
     */
    private array $textPostProcessors = [];
    private bool $initialized = false;

    public function __construct(
        private readonly ModuleRegistry $moduleRegistry,
        private readonly DependencyInjectionContainer $dic
    ) {
    }

    private function initialize(): void
    {
        foreach ($this->moduleRegistry->textPostProcessors as $postProcessorClassName)
        {
            /** @var TextPostProcessor $postProcessor */
            $postProcessor = $this->dic->createClassWithDependencyInjection($postProcessorClassName);
            $this->textPostProcessors[] = $postProcessor;
        }
    }

    public function render(string $text): string
    {
        if (!$this->initialized)
        {
            $this->initialize();
        }

        $rendered = $text;
        foreach ($this->textPostProcessors as $postProcessor)
        {
            try
            {
                $rendered = $postProcessor->process($rendered);
            }
            catch (\Throwable $e)
            {
                $logger = $this->dic->createClassWithDependencyInjection(LoggerInterface::class);
                $logger->error('Error during text postprocessing: ' . $e);
            }
        }

        return $rendered;
    }
}
