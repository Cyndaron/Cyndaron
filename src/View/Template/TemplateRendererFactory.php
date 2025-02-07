<?php
declare(strict_types=1);

namespace Cyndaron\View\Template;

use Cyndaron\Util\Util;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\Engines\EngineResolver;
use Illuminate\View\Engines\FileEngine;
use Illuminate\View\Engines\PhpEngine;
use Pine\BladeFilters\BladeFilters;
use Pine\BladeFilters\BladeFiltersCompiler;

class TemplateRendererFactory
{
    private const COMPILED_DIR = CACHE_DIR . 'template/blade';

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
        self::createCacheDir();

        $filesystem = new Filesystem();
        $viewFinder = new ViewFinder($templateRoots, $filesystem, $viewPaths);
        $compiler = new BladeCompiler(
            $filesystem,
            self::COMPILED_DIR,
        );
        $compilerEngine = new CompilerEngine($compiler, $filesystem);
        $factory = new ViewFactory($compilerEngine, $viewFinder);

        $bladeFiltersCompiler = new BladeFiltersCompiler();
        $compiler->extend(function($view) use ($bladeFiltersCompiler)
        {
            return $bladeFiltersCompiler->compile($view);
        });

        foreach (self::FILTERS as $filterName => $function)
        {
            BladeFilters::macro($filterName, $function);
        }

        return new TemplateRenderer($factory);
    }

    private static function createCacheDir(): void
    {
        Util::createDir(self::COMPILED_DIR);
    }
}
