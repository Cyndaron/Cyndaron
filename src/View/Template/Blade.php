<?php

namespace Cyndaron\View\Template;

use Illuminate\Container\Container;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Factory;
use Pine\BladeFilters\BladeFiltersCompiler;
use function call_user_func_array;

/**
 * Based on the original at https://github.com/jenssegers/blade
 * which is stated to be released under the terms of the MIT License, but does not contain a copy of that license.
 *
 * Any changes to this file are © Michael Steenbeek and available under the same license.
 */
final class Blade
{
    private Container $container;

    private Factory $factory;

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
        $this->factory = $factory;

        /** @var BladeCompiler $compiler */
        $compiler = $this->container->get('blade.compiler');
        $compiler->extend(function($view)
        {
            /** @var BladeFiltersCompiler $compiler */
            $compiler = $this->container[BladeFiltersCompiler::class];
            return $compiler->compile($view);
        });
    }

    /* @phpstan-ignore-next-line */
    public function make($view, $data = [], $mergeData = []): View
    {
        return $this->factory->make($view, $data, $mergeData);
    }

    /**
     * @param mixed[] $viewPaths
     * @param string $cachePath
     * @return void
     */
    private function setupContainer(array $viewPaths, string $cachePath): void
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
