<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Support\Arr;
use Illuminate\View\Concerns\ManagesComponents;
use Illuminate\View\Concerns\ManagesEvents;
use Illuminate\View\Concerns\ManagesFragments;
use Illuminate\View\Concerns\ManagesLayouts;
use Illuminate\View\Concerns\ManagesLoops;
use Illuminate\View\Concerns\ManagesStacks;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\ViewFinderInterface;
use InvalidArgumentException;
use function array_merge;
use function tap;
use function is_array;
use function array_keys;
use function str_ends_with;

final class ViewFactory implements FactoryContract
{
    use ManagesComponents;
    use ManagesEvents;
    use ManagesFragments;
    use ManagesLayouts;
    use ManagesStacks;
    use ManagesLoops;

    private ViewFinderInterface $finder;
    private Dispatcher $events;
    private EngineResolver $engines;

    /**
     * The number of active rendering operations.
     *
     * @var int
     */
    protected int $renderCount = 0;

    /**
     * The "once" block IDs that have been rendered.
     *
     * @var array
     */
    protected array $renderedOnce = [];

    /**
     * The extension to engine bindings.
     *
     * @var array
     */
    private array $extensions = [
        'blade.php' => 'blade',
        'php' => 'php',
        'css' => 'file',
        'html' => 'file',
    ];

    /**
     * Data that should be available to all templates.
     *
     * @var array
     */
    protected array $shared = [];

    protected function normalizeName($name): string
    {
        return $name;
    }

    public function __construct(EngineResolver $engines, ViewFinderInterface $finder, Dispatcher $events)
    {
        $this->finder = $finder;
        $this->events = $events;
        $this->engines = $engines;

        $this->share('__env', $this);
    }

    public function exists($view): bool
    {
        try
        {
            $this->finder->find($view);
        }
        catch (InvalidArgumentException)
        {
            return false;
        }

        return true;
    }

    public function file($path, $data = [], $mergeData = [])
    {
        $data = array_merge($mergeData, $this->parseData($data));

        return tap($this->viewInstance($path, $path, $data), function($view)
        {
            $this->callCreator($view);
        });
    }

    public function make($view, $data = [], $mergeData = [])
    {
        $path = $this->finder->find(
            $view = $this->normalizeName($view)
        );

        // Next, we will create the view instance and call the view creator for the view
        // which can set any data, etc. Then we will return the view instance back to
        // the caller for rendering or performing other view manipulations on this.
        $data = array_merge($mergeData, $this->parseData($data));

        return tap($this->viewInstance($view, $path, $data), function($view)
        {
            $this->callCreator($view);
        });
    }

    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value)
        {
            $this->shared[$key] = $value;
        }

        return $value;
    }

    public function addNamespace($namespace, $hints): self
    {
        $this->finder->addNamespace($namespace, $hints);

        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->finder->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * Parse the given data into a raw array.
     *
     * @param  mixed  $data
     * @return array
     */
    protected function parseData(mixed $data): array
    {
        return $data instanceof Arrayable ? $data->toArray() : $data;
    }

    /**
     * Create a new view instance from the given arguments.
     *
     * @param string $view
     * @param string $path
     * @param array|Arrayable $data
     * @return View
     */
    protected function viewInstance(string $view, string $path, array|Arrayable $data): View
    {
        return new View($this, $this->getEngineFromPath($path), $view, $path, $data);
    }

    /**
     * Get the appropriate view engine for the given path.
     *
     * @param string $path
     *@throws InvalidArgumentException
     * @return \Illuminate\Contracts\View\Engine
     *
     */
    public function getEngineFromPath(string $path): \Illuminate\Contracts\View\Engine
    {
        if (! $extension = $this->getExtension($path))
        {
            throw new InvalidArgumentException("Unrecognized extension in file: {$path}.");
        }

        $engine = $this->extensions[$extension];

        return $this->engines->resolve($engine);
    }

    /**
     * Get the extension used by the view file.
     *
     * @param string $path
     * @return string|null
     */
    protected function getExtension(string $path): string|null
    {
        $extensions = array_keys($this->extensions);

        return Arr::first($extensions, function($value) use ($path)
        {
            return str_ends_with($path, '.'.$value);
        });
    }

    /**
     * Flush all of the factory state like sections and stacks.
     *
     * @return void
     */
    public function flushState(): void
    {
        $this->renderCount = 0;
        $this->renderedOnce = [];

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
     * Get all of the shared data for the environment.
     *
     * @return array
     */
    public function getShared(): array
    {
        return $this->shared;
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
