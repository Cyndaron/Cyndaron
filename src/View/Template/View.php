<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Contracts\View\Engine;
use Illuminate\Contracts\View\View as ViewContract;
use Throwable;
use function is_array;
use function is_null;
use function array_merge;

final class View implements Htmlable, ViewContract
{
    public function __construct(
        private readonly ViewFactory $factory,
        private readonly Engine      $engine,
        private readonly string      $view,
        private readonly string      $path,
        /** @var array<string, mixed> */
        private array                $data = []
    ) {
    }

    public function toHtml(): string
    {
        return $this->render();
    }

    public function name(): string
    {
        return $this->view;
    }

    /**
     * Add a piece of data to the view.
     *
     * @phpstan-ignore-next-line
     * @param  string|array  $key
     * @param  mixed  $value
     * @return $this
     */
    public function with($key, $value = null): self
    {
        if (is_array($key))
        {
            $this->data = array_merge($this->data, $key);
        }
        else
        {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * Get the string contents of the view.
     *
     * @param  callable|null  $callback
     * @throws Throwable
     * @return string
     *
     */
    public function render(callable|null $callback = null): string
    {
        try
        {
            $contents = $this->renderContents();

            $response = isset($callback) ? $callback($this, $contents) : null;

            // Once we have the contents of the view, we will flush the sections if we are
            // done rendering all views so that there is nothing left hanging over when
            // another view gets rendered in the future by the application developer.
            $this->factory->flushStateIfDoneRendering();

            return ! is_null($response) ? $response : $contents;
        }
        catch (Throwable $e)
        {
            $this->factory->flushState();

            throw $e;
        }
    }

    /**
     * Get the contents of the view instance.
     *
     * @return string
     */
    private function renderContents(): string
    {
        // We will keep track of the number of views being rendered so we can flush
        // the section after the complete rendering operation is done. This will
        // clear out the sections for any separate views that may be rendered.
        $this->factory->incrementRender();

        $contents = $this->getContents();

        // Once we've finished rendering the view, we'll decrement the render count
        // so that each section gets flushed out next time a view is created and
        // no old sections are staying around in the memory of an environment.
        $this->factory->decrementRender();

        return $contents;
    }

    /**
     * Get the evaluated contents of the view.
     *
     * @return string
     */
    private function getContents(): string
    {
        return $this->engine->get($this->path, $this->gatherData());
    }

    /**
     * Get the data bound to the view instance.
     *
     * @return array<string, mixed>
     */
    public function gatherData(): array
    {
        $data = array_merge(['__env' => $this->factory], $this->data);

        foreach ($data as $key => $value)
        {
            if ($value instanceof Renderable)
            {
                $data[$key] = $value->render();
            }
        }

        return $data;
    }
}
