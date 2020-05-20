<?php

namespace Cyndaron\Template;

use Cyndaron\Util;
use Pine\BladeFilters\BladeFilters;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public function register(): void
    {
        parent::register();

        BladeFilters::macro('euro', static function ($value) {
            return ViewHelpers::formatEuro($value);
        });
        BladeFilters::macro('hm', static function ($value) {
            return ViewHelpers::filterHm($value);
        });
        BladeFilters::macro('dmy', static function ($value) {
            return ViewHelpers::filterDutchDate($value);
        });
        BladeFilters::macro('dmyHm', static function ($value) {
            return ViewHelpers::filterDutchDateTime($value);
        });
        BladeFilters::macro('boolToText', static function($value) {
            return ViewHelpers::boolToText($value ?? false);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder(): void
    {
        $this->app->bind('view.finder', static function ($app) {
            return new ViewFinder($app['files'], $app['config']['view.paths']);
        });
    }
}
