<?php

namespace Cyndaron\View\Template;

use Cyndaron\Util\Util;
use Pine\BladeFilters\BladeFilters;

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
}
