<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Cyndaron\Util\Util;
use Cyndaron\View\Renderer\TextRenderer;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\Factory;
use Pine\BladeFilters\BladeFilters;
use Pine\BladeFilters\BladeFiltersCompiler;

class TemplateRendererFactory
{
    private const COMPILED_DIR = 'cache/template';

    private const FILTERS = [
        'euro' => ViewHelpers::class . '::formatEuro',
        'hm' => ViewHelpers::class . '::filterHm',
        'dmy' => ViewHelpers::class . '::filterDutchDate',
        'dmyHm' => ViewHelpers::class . '::filterDutchDateTime',
        'boolToText' => ViewHelpers::class . '::boolToText',
        'boolToDingbat' => ViewHelpers::class . '::boolToDingbat',
        'slug' => Util::class . '::getSlug',
    ];

    /**
     * @param array<string, string> $templateRoots
     */
    public static function createTemplateRenderer(array $templateRoots): TemplateRenderer
    {
        $viewPaths = [];
        $cachePath = self::createCacheDir('blade');

        $events = new Dispatcher();
        $filesystem = new Filesystem();
        $viewFinder = new ViewFinder($templateRoots, $filesystem, $viewPaths);
        $resolver = new EngineResolver();
        $factory = new Factory($resolver, $viewFinder, $events);
        $fileEngine = new FileEngine($filesystem);
        $phpEngine =  new PhpEngine($filesystem);
        $compiler = new BladeCompiler(
            new Filesystem(),
            $cachePath,
        );
        $compilerEngine = new CompilerEngine($compiler, $filesystem);
        $bladeFiltersCompiler = new BladeFiltersCompiler();
        $compiler->extend(function($view) use ($bladeFiltersCompiler)
        {
            return $bladeFiltersCompiler->compile($view);
        });

        // Next, we will register the various view engines with the resolver so that the
        // environment will resolve the engines needed for various views based on the
        // extension of view file. We call a method for each of the view's engines.
        $resolver->register('file', function() use ($fileEngine)
        {
            return $fileEngine;
        });
        $resolver->register('php', function() use ($phpEngine)
        {
            return $phpEngine;
        });
        $resolver->register('blade', function() use ($compilerEngine)
        {
            return $compilerEngine;
        });

        foreach (self::FILTERS as $filterName => $function)
        {
            BladeFilters::macro($filterName, $function);
        }

        return new TemplateRenderer($factory);
    }

    private static function createCacheDir(string $engine): string
    {
        $cacheDir = self::COMPILED_DIR . '/' . $engine;
        Util::createDir($cacheDir);

        return $cacheDir;
    }
}
