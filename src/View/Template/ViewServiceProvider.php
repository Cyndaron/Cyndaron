<?php

namespace Cyndaron\View\Template;

use Cyndaron\Util\Util;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\DynamicComponent;
use Illuminate\View\Engines\CompilerEngine;
use Pine\BladeFilters\BladeFilters;
use function tap;

final class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public const FILTERS = [
        'euro' => ViewHelpers::class . '::formatEuro',
        'hm' => ViewHelpers::class . '::filterHm',
        'dmy' => ViewHelpers::class . '::filterDutchDate',
        'dmyHm' => ViewHelpers::class . '::filterDutchDateTime',
        'boolToText' => ViewHelpers::class . '::boolToText',
        'boolToDingbat' => ViewHelpers::class . '::boolToDingbat',
        'slug' => Util::class . '::getSlug',
        'parse' => ViewHelpers::class . '::parseText',
    ];

    public function register(): void
    {
        $this->registerFactory();
        $this->registerViewFinder();
        $this->registerBladeCompiler();
        $this->registerEngineResolver();

        foreach (self::FILTERS as $filterName => $function)
        {
            BladeFilters::macro($filterName, $function);
        }
    }



    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder(): void
    {
        $this->app->bind('view.finder', static function($app)
        {
            return new ViewFinder($app['files'], $app['config']['view.paths']);
        });
    }

    /**
     * Register the view environment.
     *
     * @return void
     */
    public function registerFactory(): void
    {
        $this->app->singleton('view', function($app)
        {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $factory = $this->createFactory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer($app);

            $factory->share('app', $app);

            return $factory;
        });
    }

    public function registerBladeCompiler(): void
    {
        $this->app->singleton('blade.compiler', function($app)
        {
            return tap(new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
            ), static function($blade)
            {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
        });
    }

    /**
     * Register the Blade engine implementation.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @return void
     */
    public function registerBladeEngine($resolver): void
    {
        $resolver->register('blade', function()
        {
            /** @phpstan-ignore-next-line */
            $compiler = new CompilerEngine($this->app['blade.compiler'], $this->app['files']);

            return $compiler;
        });
    }
}
