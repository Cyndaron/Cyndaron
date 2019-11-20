<?php

namespace Cyndaron\Template;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\Container as ContainerInterface;
use Illuminate\Contracts\View\Factory as FactoryContract;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Pine\BladeFilters\BladeFiltersCompiler;

/**
 * Based on the original at https://github.com/jenssegers/blade
 * which is stated to be released under the terms of the MIT License, but does not contain a copy of that license.
 *
 * Any changes to this file are © Michael Steenbeek and available under the same license.
 *
 * Class Blade
 * @package Cyndaron\Template
 */
class Blade implements FactoryContract
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var BladeCompiler
     */
    private $compiler;

    public function __construct(array $viewPaths, string $cachePath, ContainerInterface $container = null)
    {
        $this->container = $container ?: new Container;

        $this->setupContainer($viewPaths, $cachePath);
        /** @noinspection PhpParamsInspection */
        (new ViewServiceProvider($this->container))->register();

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');
        $this->compiler->extend(function ($view) {
            return $this->container[BladeFiltersCompiler::class]->compile($view);
        });
    }

    public function render(string $view, array $data = [], array $mergeData = []): string
    {
        return $this->make($view, $data, $mergeData)->render();
    }

    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    public function compiler(): BladeCompiler
    {
        return $this->compiler;
    }

    /** @noinspection PhpUnused */
    public function directive(string $name, callable $handler)
    {
        $this->compiler->directive($name, $handler);
    }

    public function exists($view): bool
    {
        return $this->factory->exists($view);
    }

    public function file($path, $data = [], $mergeData = []): View
    {
        return $this->factory->file($path, $data, $mergeData);
    }

    public function share($key, $value = null)
    {
        return $this->factory->shared($key, $value);
    }

    public function composer($views, $callback): array
    {
        return $this->factory->composer($views, $callback);
    }

    public function creator($views, $callback): array
    {
        return $this->factory->creator($views, $callback);
    }

    public function addNamespace($namespace, $hints): self
    {
        $this->factory->addNamespace($namespace, $hints);

        return $this;
    }

    public function replaceNamespace($namespace, $hints): self
    {
        $this->factory->replaceNamespace($namespace, $hints);

        return $this;
    }

    public function __call(string $method, array $params)
    {
        return call_user_func_array([$this->factory, $method], $params);
    }

    protected function setupContainer(array $viewPaths, string $cachePath)
    {
        $this->container->bindIf('files', function () {
            return new Filesystem;
        }, true);

        $this->container->bindIf('events', function () {
            return new Dispatcher;
        }, true);

        $this->container->bindIf('config', function () use ($viewPaths, $cachePath) {
            return [
                'view.paths' => $viewPaths,
                'view.compiled' => $cachePath,
            ];
        }, true);
    }
}
