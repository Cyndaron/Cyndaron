<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Illuminate\View\Concerns\ManagesComponents;
use Illuminate\View\Concerns\ManagesFragments;
use Illuminate\View\Concerns\ManagesLayouts;
use Illuminate\View\Concerns\ManagesLoops;
use Illuminate\View\Concerns\ManagesStacks;
use Illuminate\View\Engines\CompilerEngine;
use function array_merge;

final class ViewFactory
{
    use ManagesComponents;
    use ManagesFragments;
    use ManagesLayouts;
    use ManagesStacks;
    use ManagesLoops;

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    private int $renderCount = 0;

    public function __construct(
        private readonly CompilerEngine $compilerEngine,
        private readonly ViewFinder $finder
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     */
    public function file(string $path, array $data = [], array $mergeData = []): View
    {
        $data = array_merge($mergeData, $data);

        return $this->viewInstance($path, $path, $data);
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $mergeData
     */
    public function make(string $view, array $data = [], array $mergeData = []): View
    {
        $path = $this->finder->find($view);

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.
        $data = array_merge($mergeData, $data);

        return $this->viewInstance($view, $path, $data);
    }

    /**
     * Create a new view instance from the given arguments.
     *
     * @param string $view
     * @param string $path
     * @param array<string, mixed> $data
     * @return View
     */
    private function viewInstance(string $view, string $path, array $data): View
    {
        return new View($this, $this->compilerEngine, $view, $path, $data);
    }

    /**
     * Flush all of the factory state like sections and stacks.
     *
     * @return void
     */
    public function flushState(): void
    {
        $this->renderCount = 0;

        $this->flushSections();
        $this->flushStacks();
        $this->flushComponents();
        $this->flushFragments();
    }

    /**
     * Increment the rendering counter.
     *
     * @return void
     */
    public function incrementRender(): void
    {
        $this->renderCount++;
    }

    /**
     * Decrement the rendering counter.
     *
     * @return void
     */
    public function decrementRender(): void
    {
        $this->renderCount--;
    }

    /**
     * Flush all of the section contents if done rendering.
     *
     * @return void
     */
    public function flushStateIfDoneRendering(): void
    {
        if ($this->doneRendering())
        {
            $this->flushState();
        }
    }

    /**
     * Check if there are no active render operations.
     *
     * @return bool
     */
    public function doneRendering(): bool
    {
        return $this->renderCount == 0;
    }
}
