<?php

namespace Cyndaron\Template;

use Pine\BladeFilters\BladeFilters;

class ViewServiceProvider extends \Illuminate\View\ViewServiceProvider
{
    public function register()
    {
        parent::register();

        BladeFilters::macro('euro', function ($value) {
            return \Cyndaron\Util::formatEuro($value);
        });
        BladeFilters::macro('hm', function ($value) {
            return \Cyndaron\Util::filterHm($value);
        });
        BladeFilters::macro('boolToText', function($value) {
            return \Cyndaron\Util::boolToText($value ?? false);
        });
    }

    /**
     * Register the view finder implementation.
     *
     * @return void
     */
    public function registerViewFinder()
    {
        $this->app->bind('view.finder', function ($app) {
            return new ViewFinder($app['files'], $app['config']['view.paths']);
        });
    }
}
