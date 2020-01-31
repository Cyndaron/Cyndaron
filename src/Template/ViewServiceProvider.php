<?php

namespace Cyndaron\Template;

use Pine\BladeFilters\BladeFilters;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public function register(): void
    {
        parent::register();

        BladeFilters::macro('euro', static function ($value) {
            return \Cyndaron\Util::formatEuro($value);
        });
        BladeFilters::macro('hm', static function ($value) {
            return \Cyndaron\Util::filterHm($value);
        });
        BladeFilters::macro('boolToText', static function($value) {
            return \Cyndaron\Util::boolToText($value ?? false);
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
