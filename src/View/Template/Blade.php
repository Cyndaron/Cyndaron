<?php

namespace Cyndaron\View\Template;

use Closure;
use Illuminate\Container\Container;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Pine\BladeFilters\BladeFiltersCompiler;
use function assert;
use function is_string;
use function call_user_func_array;

/**
 * Based on the original at https://github.com/jenssegers/blade
 * which is stated to be released under the terms of the MIT License, but does not contain a copy of that license.
 *
 * Any changes to this file are © Michael Steenbeek and available under the same license.
 *
 * Class Blade
 * @package Cyndaron\Template
 */
final class Blade implements FactoryContract
{
    protected Container $container;

    private Factory $factory;

    private BladeCompiler $compiler;

    /**
     * Blade constructor.
     * @param string[] $viewPaths
     * @param string $cachePath
     */
    public function __construct(array $viewPaths, string $cachePath)
    {
        $this->container = new Container();

        $this->setupContainer($viewPaths, $cachePath);
        /** @noinspection PhpParamsInspection @phpstan-ignore-next-line */
        (new ViewServiceProvider($this->container))->register();

        /** @var Factory $factory */
        $factory = $this->container->get('view');
        /** @var BladeCompiler $compiler */
        $compiler = $this->container->get('blade.compiler');

        $this->factory = $factory;
        $this->compiler = $compiler;
        $this->compiler->extend(function($view)
        {
            /** @var BladeFiltersCompiler $compiler */
            $compiler = $this->container[BladeFiltersCompiler::class];
            return $compiler->compile($view);
        });
    }

    /**
     * @param string $view
     * @param mixed[] $data
     * @param mixed[] $mergeData
     * @return string
     */
    public function render(string $view, array $data = [], array $mergeData = []): string
    {
        return $this->make($view, $data, $mergeData)->render();
    }

    /* @phpstan-ignore-next-line */
    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    public function compiler(): BladeCompiler
    {
        return $this->compiler;
    }

    public function directive(string $name, callable $handler): void
    {
        $this->compiler->directive($name, $handler);
    }

    public function exists($view): bool
    {
        return $this->factory->exists($view);
    }

    /* @phpstan-ignore-next-line */
    public function file($path, $data = [], $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    /**
     * @param mixed[]|string $key
     * @param mixed $value
     * @return mixed|void
     */
    public function share($key, $value = null)
    {
        assert(is_string($key));
        return $this->factory->shared($key, $value);
    }

    /**
     * @param mixed[]|string $views
     * @param Closure|string $callback
     * @return mixed[]
     */
    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    /**
     * @param mixed[]|string $views
     * @param Closure|string $callback
     * @return mixed[]
     */
    public function creator($views, $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    /**
     * @param string $namespace
     * @param mixed[]|string $hints
     * @return $this
     */
    public function addNamespace($namespace, $hints): self
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    /**
     * @param string $namespace
     * @param mixed[]|string $hints
     * @return $this
     */
    public function replaceNamespace($namespace, $hints): self
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    /**
     * @param string $method
     * @param mixed[] $params
     * @return mixed
     */
    public function __call(string $method, array $params)
    {
        /** @phpstan-ignore-next-line (false positive) */
        return call_user_func_array([$this->factory, $method], $params);
    }

    /**
     * @param mixed[] $viewPaths
     * @param string $cachePath
     * @return void
     */
    protected function setupContainer(array $viewPaths, string $cachePath): void
    {
        $this->container->bindIf('files', static function()
        {
            return new Filesystem();
        }, true);

        $this->container->bindIf('events', static function()
        {
            return new Dispatcher();
        }, true);

        $this->container->bindIf('config', static function() use ($viewPaths, $cachePath)
        {
            return [
                'view.paths' => $viewPaths,
                'view.compiled' => $cachePath,
            ];
        }, true);
    }
}
