<?php

namespace Cyndaron\View\Template;

use Pine\BladeFilters\BladeFilters;

final class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public const FILTERS = [
        'euro' => ViewHelpers::class . '::formatEuro',
        'hm' => ViewHelpers::class . '::filterHm',
        'dmy' => ViewHelpers::class . '::filterDutchDate',
        'dmyHm' => ViewHelpers::class . '::filterDutchDateTime',
        'boolToText' => ViewHelpers::class . '::boolToText',
    ];

    public function register(): void
    {
        parent::register();

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
